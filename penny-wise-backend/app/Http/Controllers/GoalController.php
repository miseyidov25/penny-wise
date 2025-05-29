<?php

namespace App\Http\Controllers;
use App\Models\Goal;
use Illuminate\Http\Request;
use App\Models\Wallet;

class GoalController extends Controller
{
   public function index()
    {
        // Return all goals for the authenticated user with their wallets
        return auth()->user()->goals()->with('wallet')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
            'wallet_id' => 'required|exists:wallets,id',
        ]);

        $wallet = auth()->user()->wallets()->where('id', $data['wallet_id'])->first();
        
        if (!$wallet) {
            abort(403, 'You do not own this wallet.');
        }

        // Ensure the wallet belongs to the authenticated user
        if (!auth()->user()->wallets()->where('id', $data['wallet_id'])->exists()) {
            abort(403, 'You do not own this wallet.');
        }

        $goal = auth()->user()->goals()->create([
        'name' => $data['name'],
        'target_amount' => $data['target_amount'],
        'deadline' => $data['deadline'] ?? null,
        'wallet_id' => $wallet->id,
        'currency' => $wallet->currency, // use wallet's default currency here
        'current_amount' => 0,
        'is_completed' => false,
    ]);

        return response()->json($goal, 201);
    }

    public function show(Goal $goal)
    {
        $this->authorizeGoal($goal);
        return $goal->load('wallet');
    }

    public function update(Request $request, Goal $goal)
    {
        $this->authorizeGoal($goal);

        $data = $request->validate([
            'name' => 'string|max:255',
            'target_amount' => 'numeric|min:0',
            'current_amount' => 'numeric|min:0',
            'currency' => 'string|size:3',
            'deadline' => 'nullable|date',
            'is_completed' => 'boolean',
        ]);

        $goal->update($data);

        return response()->json($goal);
    }

    public function destroy(Goal $goal)
    {
        $this->authorizeGoal($goal);
        $goal->delete();

        return response()->json(null, 204);
    }

    protected function authorizeGoal(Goal $goal)
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
