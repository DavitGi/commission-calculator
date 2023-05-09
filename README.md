## Commisosn Calculator

_____

### About
This is a simple commission calculator that takes in a CSV file of transactions and calculates the commission for each transaction.


### How to run
1. Clone the repository:
2. Run:
    ```bash
    composer install && cp .env.example .env
    ```
3. Set the `EXCHANGE_RATE_URL` in the `.env` file to the URL of the exchange rate API.
4. Copy the CSV file to the `storage` directory (There is one sample file `transaction.csv`, you can use that or put your own file).
5. Run:
    ```bash
    php artisan commission:fee {file}
    ```
   where `{file}` is the file name to the CSV file.
   (For example `php artisan commission:fee transaction.csv`).

### How to run tests
1. Run:
    ```bash
    php artisan test
    ```
