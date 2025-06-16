import axios from 'axios';
import React, { useCallback,useEffect, useState } from 'react';

import AddGoalForm from './../features/transactions/add-goal-dialog';

interface Goal {
  id: number;
  name: string;
  target_amount: number;
  current_amount: number;
  currency: string;
  deadline: string;
  is_completed: boolean;
}

interface GoalsProps {
  walletId: number;
}

const Goals: React.FC<GoalsProps> = ({ walletId }) => {
  const [goals, setGoals] = useState<Goal[]>([]);
  const [showForm, setShowForm] = useState(false);

  const fetchGoals = useCallback(async () => {
    if (!walletId) return;

    try {
      const response = await axios.get(
        `${process.env.NEXT_PUBLIC_BACKEND_URL}/api/goals?wallet_id=${walletId}`,
        {
          headers: {
            Accept: 'application/json',
          },
          withCredentials: true,
        }
      );
      setGoals(response.data);
    } catch (error) {
      console.error('Error fetching goals:', error);
    }
  }, [walletId]); // ✅ wrapped with useCallback, stable reference

  const handleDelete = async (goalId: number) => {
    if (!confirm('Are you sure you want to delete this goal?')) return;

    try {
      await axios.delete(`${process.env.NEXT_PUBLIC_BACKEND_URL}/api/goals/${goalId}`, {
        withCredentials: true,
      });
      fetchGoals(); // ✅ no linter warnings now
    } catch (error) {
      console.error('Failed to delete goal:', error);
      alert('Failed to delete goal');
    }
  };

  useEffect(() => {
    fetchGoals(); // ✅ stable due to useCallback
  }, [fetchGoals]); // ✅ linter is happy

  return (
    <div style={styles.panel}>
    <div style={styles.header}>
      <h2 style={{ margin: 0 }}>🎯 Goals</h2>
      <button 
        onClick={() => setShowForm(!showForm)} 
        className="px-4 py-2 rounded-md bg-primary text-primary-foreground"
      >
        {showForm ? '✖ Close' : '➕ Add Goal'}
      </button>

    </div>

    {showForm && (
      <div style={{ marginBottom: '1rem' }}>
        <AddGoalForm
          walletId={walletId}
          onGoalCreated={() => {
            fetchGoals();
            setShowForm(false);
          }}
        />
      </div>
    )}

      {goals.length > 0 ? (
        <ul style={{ paddingLeft: 0 }}>
          {goals.map((goal) => (
            <li key={goal.id} style={styles.goalItem}>
              <div style={styles.goalContent}>
                <div>
                  <strong>{goal.name}</strong><br />
                  {goal.current_amount} / {goal.target_amount} {goal.currency}<br />
                  <small>Deadline: {new Date(goal.deadline).toLocaleDateString()}</small><br />
                  <progress value={goal.current_amount} max={goal.target_amount}></progress>
                </div>
                <button
                  style={styles.deleteButton}
                  onClick={() => handleDelete(goal.id)}
                  aria-label={`Delete goal ${goal.name}`}
                >
                  🗑️
                </button>
              </div>
            </li>
          ))}
        </ul>
      ) : (
        <p>No goals yet.</p>
      )}
    </div>
  );
};

const styles: Record<string, React.CSSProperties> = {
  panel: {
    position: 'fixed',
    right: 0,
    top: '60px',
    width: '300px',
    padding: '1rem',
    boxShadow: '-2px 0 6px rgba(0,0,0,0.1)',
    height: 'calc(100vh - 60px)',
    overflowY: 'auto',
    zIndex: 1000,
  },
  goalItem: {
    marginBottom: '1rem',
    listStyleType: 'none',
  },
  goalContent: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  deleteButton: {
    background: 'transparent',
    border: 'none',
    cursor: 'pointer',
    fontSize: '1.2rem',
    color: 'red',
  },
  toggleButton: {
    color: '#fff',
    border: 'none',
    padding: '0.5rem 1rem',
    borderRadius: '4px',
    cursor: 'pointer',
    marginBottom: '0.5rem',
  },
  header: {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginBottom: '0.5rem',
  },

};

export default Goals;
