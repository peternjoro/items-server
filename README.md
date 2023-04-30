# items-server

To run the api-server locally, 
  1. Make sure you have composer installed, https://getcomposer.org/, add composer to your PATH environment variable
  2. Make sure you have xampp/wamp/mamp with at least php version 8.1, add php path to your PATH environament variable
  3. Clone the repository inside the root folder of the server you are using, htdocs for xampp, www for wamp
  4. Create a mysql database named user_items
  5. Import the SQL dump file inside the backup folder of the project
  6. Update your database credentials inside the .env file
  7. Open your terminal inside the project directory
  8. Run these commands;
      a) composer install
      b) composer dump-autoload
      c) npm install
      d) npm run dev
      
  9. To run the api, run your server, e.g xampp/wamp
  10. Run this command from the terminal inside the project directory, php artisan server, the server should start running on port 8000, if a different port is used the        frontend will not be able to access the server
  
