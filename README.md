# PennyWise

PennyWise is a budget management web platform designed to help users efficiently manage their finances through a wallet system. The application consists of two main parts:

- **Backend:** Laravel API handling authentication, wallets, transactions, and categories.
- **Frontend:** Next.js React app for the user interface.

## Repository Structure

Clone the repository:

```bash
git clone https://github.com/miseyidov25/penny-wise.git
```

Project structure:

```
pennywise/
├── penny-wise-backend/   # Laravel backend API
└── penny-wise-frontend/  # Next.js frontend app
```

---

## Getting Started

### Backend

The backend is located in the `penny-wise-backend` folder.

To get started:

```bash
cd penny-wise-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

The backend API will run at [http://localhost:8000](http://localhost:8000).

For detailed instructions, see [`penny-wise-backend/README.md`](penny-wise-backend/README.md).

---

### Frontend

The frontend is located in the `penny-wise-frontend` folder.

To start the development server:

```bash
cd penny-wise-frontend
npm install       # or yarn, pnpm, bun
npm run dev       # or yarn dev, pnpm dev, bun dev
```

Open [http://localhost:3000](http://localhost:3000) in your browser.

For detailed instructions, see [`penny-wise-frontend/README.md`](penny-wise-frontend/README.md).

---

## Environment Configuration

Make sure the frontend `.env` file has the correct backend URL set, for example:

```env
NEXT_PUBLIC_BACKEND_URL=http://localhost:8000
```

---

## Summary

This project allows users to:

- Create and manage wallets
- Track transactions and categorize expenses
- Set financial goals with deadlines
- View wallet balances and progress

Happy budgeting with PennyWise! 🎯
