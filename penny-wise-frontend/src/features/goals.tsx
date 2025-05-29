import axios from 'axios';
import React, { useEffect, useState } from 'react';

interface Goal {
  id: number;
  name: string;
  target_amount: number;
  current_amount: number;
  currency: string;
  deadline: string;
  is_completed: boolean;
}

const Goals: React.FC = () => {
  const [goals, setGoals] = useState<Goal[]>([]);

  const fetchGoals = async () => {
  try {
    const response = await axios.get('/api/goals', {
      headers: {
        Accept: 'application/json',
      },
    });
    setGoals(response.data);
  } catch (error) {
    console.error('Error fetching goals:', error);
  }
};

  useEffect(() => {
    fetchGoals();
  }, []);

  return (
    <div style={styles.panel}>
      <h2>🎯 Goals</h2>
      {goals.length > 0 ? (
        <ul>
          {goals.map((goal) => (
            <li key={goal.id} style={styles.goalItem}>
              <strong>{goal.name}</strong><br />
              {goal.current_amount} / {goal.target_amount} {goal.currency}<br />
              <progress value={goal.current_amount} max={goal.target_amount}></progress>
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
    background: '#f8f8f8',
    padding: '1rem',
    boxShadow: '-2px 0 6px rgba(0,0,0,0.1)',
    height: 'calc(100vh - 60px)',
    overflowY: 'auto',
    zIndex: 1000,
  },
  goalItem: {
    marginBottom: '1rem',
  },
};

export default Goals;
