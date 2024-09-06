# Laravel Broadcasting and Vue3

This project provides a foundation for building a real-time chat application using Laravel, Laravel Reverb (a Pusher alternative), and Vue 3. It enables users to connect, send messages, and receive updates in real-time, fostering a dynamic and engaging chat experience.

## Installation:

- Clone the repository
```shell
git clone https://github.com/explore-pu/laravel-broadcasting.git
```

- Navigate to the project directory
```shell
cd laravel-broadcasting
```

- Install dependencies
```shell
composer install
```

- Copy the `.env` file
```shell
cp .env.example .env
```

- Generate application key
```shell
php artisan key:generate
```

- Edit `.env` file, Configure database connection and Reverb parameters
> REVERB_APP_ID=
> 
> REVERB_APP_KEY=
> 
> REVERB_APP_SECRET=

- Migrate database tables
```shell
php artisan migrate
```

- Establish a soft connection to the storage
```shell
php artisan storage:link
```

- start reverb server
```shell
php artisan reverb:start
```

- Start development server
```shell
npm install && npm run dev
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
