<?php

namespace App\Http\Controllers;
use App\Models\Goal;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index()
{
    return auth()->user()->goals()->get();
}

public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'target_amount' => 'required|numeric|min:0',
        'currency' => 'required|string|size:3',
        'deadline' => 'nullable|date',
    ]);

    $goal = auth()->user()->goals()->create($data);

    return response()->json($goal, 201);
}

public function show(Goal $goal)
{
    $this->authorizeGoal($goal);
    return $goal;
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
