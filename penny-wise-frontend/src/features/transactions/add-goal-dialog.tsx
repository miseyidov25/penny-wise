import axios from 'axios';
import React, { useState } from 'react';
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button"

interface AddGoalFormProps {
  walletId: number;
  onGoalCreated: () => void;
}

const AddGoalForm: React.FC<AddGoalFormProps> = ({ walletId, onGoalCreated }) => {
  const [form, setForm] = useState({
    name: '',
    target_amount: '',
    deadline: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    const { name, target_amount, deadline } = form;

    if (!name || !target_amount || !deadline) {
      setError('All fields are required');
      setLoading(false);
      return;
    }

    try {
    await axios.post(
        `${process.env.NEXT_PUBLIC_BACKEND_URL}/api/goals`,
        {
        name,
        target_amount: parseFloat(target_amount),
        deadline,
        wallet_id: walletId,
        },
        { withCredentials: true }
    );
    onGoalCreated();
    setForm({ name: '', target_amount: '', deadline: '' });
    } catch (err: unknown) {
    let backendMessage = 'Failed to create goal';

    if (axios.isAxiosError(err)) {
        backendMessage = err.response?.data?.message || backendMessage;
    } else if (err instanceof Error) {
        backendMessage = err.message;
    }

    setError(backendMessage);
    } finally {
    setLoading(false);
    }

  };

  return (
    <form
      onSubmit={handleSubmit}
      style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}
    >
      <Input
        name="name"
        placeholder="Goal name"
        value={form.name}
        onChange={handleChange}
      />
      <Input
        type="number"
        name="target_amount"
        placeholder="Target amount"
        value={form.target_amount}
        onChange={handleChange}
      />
      <Input
        type="date"
        name="deadline"
        value={form.deadline}
        onChange={handleChange}
      />
      {error && <div style={{ color: 'red' }}>{error}</div>}
      <Button type="submit" disabled={loading}>
        {loading ? 'Creating...' : 'Create Goal'}
      </Button>
    </form>
  );
};

export default AddGoalForm;
