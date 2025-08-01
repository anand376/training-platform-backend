@echo off
echo Running Laravel Training Platform Tests...
echo.

echo Running Feature Tests...
php artisan test --testsuite=Feature
echo.

echo Running Unit Tests...
php artisan test --testsuite=Unit
echo.

echo Running All Tests...
php artisan test
echo.

echo Tests completed!
pause 