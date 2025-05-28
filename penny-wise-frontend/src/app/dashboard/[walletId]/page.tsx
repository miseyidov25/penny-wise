"use client";

import {
  GearIcon,
  MagnifyingGlassIcon,
  QuestionMarkIcon,
} from "@radix-ui/react-icons";
import Link from "next/link";
import { useEffect, useRef } from "react";
import { toast } from "sonner";

import { Header } from "@/components/header";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { buttonVariants } from "@/components/ui/button";
import {
  HoverCard,
  HoverCardContent,
  HoverCardTrigger,
} from "@/components/ui/hover-card";
import { Skeleton } from "@/components/ui/skeleton";
import { AddTransactionDialog } from "@/features/transactions/add-transaction-dialog";
import { AddRecurringTransactionDialog } from "@/features/transactions/add-recurring-dialog";
import { columns } from "@/features/transactions/columns";
import { DataTable } from "@/features/transactions/data-table";
import { DeleteWalletDialog } from "@/features/transactions/delete-wallet-dialog";
import { TransactionTabs } from "@/features/transactions/transaction-tabs";
import { UpdateWalletDialog } from "@/features/transactions/update-wallet-dialog";
import { useWallet } from "@/features/transactions/use-wallet";
import { useAuth } from "@/hooks/auth";
import { Download, FileUp } from 'lucide-react';
import type { AddRecurringTransactionPayload } from "@/features/transactions/types";

export default function Wallet({ params }: { params: { walletId: string } }) {
  useAuth({ middleware: "auth" });

  const {
    addTransaction,
    categories,
    deleteTransaction,
    deleteWallet,
    error,
    isPending,
    updateWallet,
    wallet,
  } = useWallet(params.walletId);

  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleImportClick = () => {
    fileInputRef.current?.click();
  };

  const handleFileChange = async (event: React.ChangeEvent<HTMLInputElement>) => {
  const file = event.target.files?.[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL}/api/transactions/import`, {
      method: 'POST',
      headers: {
        "Accept": "application/json", // specify expected response type
      },
      credentials: 'include',
      body: formData,
    });

    if (!response.ok) {
      throw new Error('Failed to import transactions');
    }

    alert('Transactions imported successfully!');
    // Optionally refresh data
  } catch (error) {
    console.error(error);
    alert('Import failed');
  }
};

  const handleExport = async (): Promise<void> => {
  try {
    const response = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL}/api/transactions/export`, {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      },
    });

    if (!response.ok) {
      throw new Error('Failed to export transactions');
    }

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    if (!wallet?.name) {
      alert("Wallet not loaded yet.");
      return;
    }
    const safeName = (wallet.name ?? 'wallet').replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_-]/g, '');
    const a = document.createElement('a');
    a.href = url;
    a.download = `${wallet.name}_transactions.xlsx`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error(error);
    alert('Export failed');
  }
};

  useEffect(() => {
    if (error) {
      toast.error(error);
    }
  }, [error]);

  async function addRecurringTransaction(payload: AddRecurringTransactionPayload) {
  try {
    const response = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL}/api/recurring-transactions`, {
      method: "POST",
      credentials: 'include', // important if backend uses cookie-based auth
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json", // specify expected response type
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      let errorData;
      try {
        errorData = await response.json();
      } catch (e) {
        console.error('Failed to parse error response as JSON', e);
        return { error: `Unexpected error (status ${response.status})` };
      }
      console.error('Validation errors:', errorData);
      return { error: errorData.message || "Failed to create recurring transaction" };
    }

    return undefined;
  } catch (error) {
    console.error('Fetch error:', error);
    return { error: "Something went wrong while creating recurring transaction." };
  }
}

  return (
    <div className="grid min-h-screen grid-rows-[auto,_1fr,_auto]">
      <Header isAuthorized>
        <Link
          href="/settings/profile"
          className={buttonVariants({ variant: "outline", size: "icon" })}
        >
          <GearIcon />
        </Link>
      </Header>

      <main className="container max-w-screen-sm py-8">
        <div className="mb-8 flex items-center justify-between gap-4">
          <h1 className="text-4xl font-extrabold tracking-tight">
            {wallet?.name}
          </h1>

          {wallet && (
            <div className="flex items-center gap-2">
              <HoverCard>
                <HoverCardTrigger className="inline-flex h-9 w-9 items-center justify-center">
                  <QuestionMarkIcon />
                </HoverCardTrigger>

                <HoverCardContent>
                  <h3 className="font-medium">How does it work?</h3>

                  <p className="mt-2 text-sm">
                    This is your wallet. Here you can add, update, or delete
                    transactions.
                  </p>

                  <p className="mt-2 text-sm">
                    Get a clear picture of your financial situation by comparing
                    your income and expenses, or by category.
                  </p>

                  <p className="mt-2 text-sm">
                    View all transactions for this wallet, and sort them by date
                    or amount.
                  </p>
                </HoverCardContent>
              </HoverCard>

              <button
                onClick={handleExport}
                className="p-2 hover"
                title="Export Transactions"
              >
                <FileUp className="w-5 h-5" />
              </button>

             <input
                type="file"
                accept=".xlsx"
                ref={fileInputRef}
                style={{ display: 'none' }}
                onChange={handleFileChange}
              />

              <button
                onClick={handleImportClick}
                className="p-2 hover"
                title="Import Transactions"
              >
                <Download className="w-5 h-5" />
              </button>

              <UpdateWalletDialog wallet={wallet} updateWallet={updateWallet} />
              <DeleteWalletDialog wallet={wallet} deleteWallet={deleteWallet} />
            </div>
          )}
        </div>

        {isPending && <Skeleton className="h-96 bg-card" />}

        {!isPending && wallet && (
          <section className="space-y-4">
            <TransactionTabs wallet={wallet} />

            <div className="flex items-center gap-x-2">
              <AddTransactionDialog
                addTransaction={addTransaction}
                categories={categories}
              />

              <AddRecurringTransactionDialog
                walletId={wallet.id}
                addRecurringTransaction={addRecurringTransaction}
                categories={categories}
              />
            </div>

            {wallet.transactions.length === 0 ? (
              <Alert variant="default">
                <MagnifyingGlassIcon />

                <AlertTitle>No transactions</AlertTitle>

                <AlertDescription>
                  No transactions found. Click the button below to add a new
                  transaction.
                </AlertDescription>
              </Alert>
            ) : (
              <div className="max-w-[calc(100vw-2rem)] overflow-x-auto whitespace-nowrap">
                <DataTable
                  columns={columns}
                  data={wallet.transactions.map((transaction) => ({
                    ...transaction,
                    deleteRow: () => deleteTransaction(transaction.id),
                  }))}
                />
              </div>
            )}
          </section>
        )}
      </main>
    </div>
  );
}
