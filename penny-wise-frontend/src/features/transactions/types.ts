export interface Transaction {
  id: number;
  category_name: string;
  amount: string;
  description: string;
  date: string;
  currency: string;
}

export interface Wallet {
  id: number;
  name: string;
  balance: string;
  created_at: string;
  updated_at: string;
  currency: string;
  transactions: Transaction[];
}

export interface Category {
  id: number;
  user_id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

export interface AddTransactionPayload {
  category_name: string;
  amount: string;
  description: string;
  date: string;
}

export interface AddWalletPayload {
  name: string;
  currency: string;
  balance: string;
}

export interface UpdateWalletPayload {
  name: string;
  currency: string;
}

// For your frontend form data (with start_date)
export type AddRecurringTransactionFormPayload = Omit<AddRecurringTransactionPayload, 'next_run' | 'wallet_id'> & {
  start_date: string;
};

// For your backend API request (with next_run)
export type AddRecurringTransactionPayload = {
  wallet_id: number;
  category_name: string;
  amount: string; // or number if form returns number
  description: string;
  interval: "daily" | "weekly" | "monthly" | "yearly";
  start_date: string;
  end_date?: string;
  next_run: string;
};


