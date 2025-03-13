# PHP Work Schedule App

This project is a simple PHP application designed to manage the work schedule of employees. It allows for the registration of workers and tracks their entry and exit times based on their employee numbers.

## Project Structure

```
php-work-schedule-app
├── src
│   ├── index.php          # Main entry point for the application
│   ├── register.php       # Handles registration of new workers
│   ├── check.php          # Checks worker numbers and records entry/exit times
│   └── workers.json       # Stores worker data in JSON format
├── public
│   └── index.html         # User interface for the application
├── composer.json          # Composer configuration file
└── README.md              # Project documentation
```

## Features

- **Worker Registration**: Add new workers to the system with a unique 5-digit employee number.
- **Time Tracking**: Record entry and exit times for workers using their 5-digit employee number and a 3-digit code.
- **Data Storage**: All worker data is stored in a JSON file for easy access and modification.

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/php-work-schedule-app.git
   ```
2. Navigate to the project directory:
   ```
   cd php-work-schedule-app
   ```
3. Install dependencies using Composer:
   ```
   composer install
   ```

## Usage

1. Open `public/index.html` in your web browser.
2. Use the form to register new workers or to clock in/out existing workers.
3. The application will handle the logic of recording entry and exit times based on the provided employee numbers.

## Contributing

Feel free to submit issues or pull requests if you have suggestions for improvements or new features.

## License

This project is open-source and available under the MIT License.