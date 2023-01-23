Application for management of Books, Authors, Genres and Users
===========

This is a test application for Symfony 5.4 & Flex showcasing an API for interacting with a book, author, genre and user catalog.

Installation
------------

 1. Clone the repository

    ```
    git clone git@gitlab.com:user1387484241/user138748424.git
    ```

2. Install dependencies

    2.1
		```
		composer install
		```

	2.2
		```
		npm install
		```

3. Setup database

    Please make sure you change your local .env file according to the comment in the doctrine/doctrine-bundle section.

    ```
    bin/console doctrine:schema:update --force
    bin/console doctrine:fixtures:load
    ```

4. (Optional) Run a web server

    ```
    bin/console server:run
    ```
