"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { PlusIcon,ReloadIcon } from "@radix-ui/react-icons";
import { useTransition } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";

import { AutocompleteInput } from "@/components/ui/autocomplete-input";
import { Button, buttonVariants } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

import { addRecurringTransactionSchema } from "./schemas";
import type { AddRecurringTransactionPayload } from "./types";

export function AddRecurringTransactionDialog({
  walletId,
  addRecurringTransaction,
  categories,
}: {
  walletId: number;
  addRecurringTransaction: (
    payload: AddRecurringTransactionPayload,
  ) => Promise<{ error: string } | undefined>;

  categories: string[];
}) {
  const form = useForm<AddRecurringTransactionPayload>({
    resolver: zodResolver(addRecurringTransactionSchema),
    defaultValues: {
      category_name: "",
      amount: "0.00",
      description: "",
      interval: "monthly",
      next_run: new Date().toISOString().split("T")[0],
      end_date: "",
    },
  });

  const [isPending, startTransition] = useTransition();

  function onSubmit(values: AddRecurringTransactionPayload) {
    startTransition(async () => {
      const result = await addRecurringTransaction({
      ...values,
      wallet_id: walletId, // 👈 inject wallet_id into the payload
    });

      if (result?.error) {
        toast.error(result.error);
        console.log(result);
      } else {
        toast.success("Recurring transaction added");
        form.reset();
      } 
    });
  }

  return (
    <Dialog>
      <DialogTrigger className={buttonVariants()}>
        <PlusIcon />
        <span className="ml-2">Add recurring transaction</span>
      </DialogTrigger>

      <DialogContent>
        <DialogHeader>
          <DialogTitle>Add recurring transaction</DialogTitle>
          <DialogDescription>
            This will automatically generate new transactions in the future.
          </DialogDescription>
        </DialogHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="amount"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Amount</FormLabel>
                  <FormControl>
                    <Input
                      placeholder="29.99"
                      type="number"
                      step=".01"
                      {...field}
                    />
                  </FormControl>
                  <FormDescription>
                    Use a negative value for expenses.
                  </FormDescription>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="category_name"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Category</FormLabel>
                  <AutocompleteInput
                    setValue={(value) =>
                      form.setValue("category_name", value)
                    }
                    value={field.value}
                    options={categories}
                  />
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="description"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Description</FormLabel>
                  <FormControl>
                    <Input
                      placeholder="Gym membership, rent, salary, etc."
                      {...field}
                    />
                  </FormControl>
                  <FormDescription>
                    Optional description of the recurring transaction.
                  </FormDescription>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="interval"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Interval</FormLabel>
                  <Select onValueChange={field.onChange} value={field.value}>
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Select interval" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="daily">Daily</SelectItem>
                      <SelectItem value="weekly">Weekly</SelectItem>
                      <SelectItem value="monthly">Monthly</SelectItem>
                      <SelectItem value="yearly">Yearly</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="next_run"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Start Date</FormLabel>
                  <FormControl>
                    <Input type="date" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="end_date"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>End Date (optional)</FormLabel>
                  <FormControl>
                    <Input type="date" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <Button type="submit" className="mt-4" disabled={isPending}>
              {isPending && <ReloadIcon className="mr-2 animate-spin" />}
              <span>Add recurring transaction</span>
            </Button>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}
