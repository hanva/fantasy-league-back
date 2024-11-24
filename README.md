This is a [Next.js](https://nextjs.org/) project bootstrapped
with [`create-next-app`](https://github.com/vercel/next.js/tree/canary/packages/create-next-app).

## Getting Started

Install Composer

```bash
composer install
```

Replace the informations in the .env

```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db-name"
```

List of Commands :

```bash
php bin/console app:league-job-command
```

```bash
php bin/console app:card-job-command cards.json
```

```bash
php bin/console app:event-job-command
```

```bash
php bin/console app:bet-job-command > logs.json // WIP
```