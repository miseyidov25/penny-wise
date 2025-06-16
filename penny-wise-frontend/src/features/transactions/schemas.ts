import { z } from "zod";

export const addTransactionSchema = z.object({
  category_name: z.string().min(1, "Category is required").max(255),
  amount: z
    .string()
    .refine((val) => /^-?\d+(\.\d{1,2})?$/.test(val.toString()), {
      message: "Amount must be a number with up to two decimal places",
    })
    .refine(
      (val) => Number(val) < 100000000.0,
      "Amount must not exceed 100000000.00",
    )
    .refine(
      (val) => Number(val) > -100000000.0,
      "Amount must exceed -100000000.00",
    )
    .refine((val) => Number(val) !== 0, "Amount must not be zero"),
  description: z.string().max(255).optional(),
  date: z.string().transform((value) => new Date(value).toISOString()),
});

export const addRecurringTransactionSchema = z.object({
  category_name: z.string().min(1, "Category is required"),
  amount: z.string().refine((val) => !isNaN(parseFloat(val)), {
    message: "Invalid number",
  }),
  description: z.string().optional(),
  interval: z.enum(["daily", "weekly", "monthly", "yearly"]),
  start_date: z.string().min(1, "Start date is required"),
  end_date: z.string().optional(),
});

export const addRecurringTransactionFormSchema = z.object({
  category_name: z.string().min(1),
  amount: z.string().min(1),
  description: z.string().optional(),
  interval: z.enum(['daily', 'weekly', 'monthly', 'yearly']),
  start_date: z.string().min(1), // required
  end_date: z.string().optional(),
});

export const addWalletSchema = z.object({
  name: z.string().min(1, "Name is required").max(255),
  currency: z.string().length(3, "Currency must be a 3-letter code"),
  balance: z
    .string()
    .refine((val) => /^-?\d+(\.\d{1,2})?$/.test(val.toString()), {
      message: "Balance must be a number with up to two decimal places",
    }),
});

export const updateWalletSchema = z.object({
  name: z.string().min(1, "Name is required").max(255),
  currency: z.string().length(3, "Currency must be a 3-letter code"),
});
