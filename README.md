# Database name
news_parse

# Run server
php -S localhost:8000 -t public

# To install project depedencies, run the code below on the project directory
php composer.phar update
php composer.phar install

# There are 4 routes
1. Home (localhost:8000)
2. Cron job (localhost:8000/cron_job/news)
3. Admin Dashboard (localhost:8000/admin/dashboard)
4. Delete Post (localhost:8000/delete/{id}/news)
5. Login (localhost:8000/login)
6. Register (localhost:8000/register)

# Login

Only admin "ROLE_ADMIN" can login and delete post.
# Email: admin@qwerty.com
# Password: qwerty

