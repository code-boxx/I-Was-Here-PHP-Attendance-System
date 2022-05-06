## I WAS HERE
I Was Here is a simple, free, and open-source PHP Student Attendance Management System. Not the best in the world, but it will help small schools to get started quickly without all the hassle.
<br><br>


## SCREENSHOTS
<p float="left">
  <img width="250" style="inline-block" src="https://github.com/code-boxx/I-Was-Here/blob/main/assets/iwh-ss-1.png">
  <img width="250" style="inline-block" src="https://github.com/code-boxx/I-Was-Here/blob/main/assets/iwh-ss-2.png">
  <img width="250" style="inline-block" src="https://github.com/code-boxx/I-Was-Here/blob/main/assets/iwh-ss-3.png">
  <img width="250" style="inline-block" src="https://github.com/code-boxx/I-Was-Here/blob/main/assets/iwh-ss-4.png">
  <img width="250" style="inline-block" src="https://github.com/code-boxx/I-Was-Here/blob/main/assets/iwh-ss-5.png">
</p><br>


## INSTALLATION & DOCUMENTATION
Just access `index.php` and walk through the installer.

Visit https://code-boxx.com/i-was-here-php-attendance-system/ for the documentation.
<br><br>


## UPDATING
* If your existing copy has an `options` table - Just override all the existing files and access `index.php`. The installer will take care of database updates (if any).
* If not – Manually import the `options` table from `SQL-iwashere.sql.sql`, create a `IWH_VER` entry with value of `0` and group `0`. Thereafter, just copy the new files and let the installer do the magic.
<br><br>


## REQUIREMENTS
1) Not extensively tested, but should work with at least PHP 8.0.
2) PHP MYSQL PDO extension.
3) Apache server with MOD REWRITE enabled.
4) "Grade A" browser.
<br><br>


## LICENSE
Copyright by Code Boxx

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
