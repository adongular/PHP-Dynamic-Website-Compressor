# Dynamic Website Compressor

*   Developer : Abhimanyu Sharma ( abhimanyu003 )
*   Email: abhimanyusharma003@gmail.com
*   Profile URL: [http://codecanyon.net/user/abhimanyu003](http://codecanyon.net/user/abhimanyu003)


**Table of contents**

[TOC]

## 1 - Installation

*   Login to your FTP account
*   Upload `compressor.php` to your `public_html` or `www` folder.
*   Open `index.php` file and add below line just after `<?php` tags.

        include_once('compressor.php');
*   You are done

## 2 - Escapes Tag

Use these tags if you want to prevent parts of your **HTML - inline JS - inline CSS** from getting compressed. The content between these tags will not get compressed or touched by compressor.

    <dwcescape> </dwcescape>

**Example**

    <dwcescape>
    <p>This part of HTML will not be get compressed</p>
    <p>New line will not get remove</p>
    </dwcescape>

## 3 - Google AdSense

Wrap you Google AdSense code between `<dwcescape></dwcescape>` tags

**Example**

    <dwcescape> 
        Place your google ads code here
    </dwcescape>


## 4 - Extra optimization using .htaccess

1. Open your .htaccess file
2. Visit this url https://gist.github.com/abhimanyu003/ed3ff2c2a171fb0ca6a81c6fe9a6465f
3. Copy and paste the codes at the bottom of your .htaccess file
4. It will optimize your website to more extent.


## 5 - FAQ

**Q. My .js .css files are not getting compressed.**

**A.** The main purpose of Dynamic Website Compressor is to minify **HTML - inline JS - inline CSS** on the fly. It will not minify files. ( There is difference between inline JS - inline CSS and .js .css files )

**Q. Tool that I can use to minify files**

**A.** You can use http://refresh-sf.com/yui/ to minify files.

**Q. Can I use Dynamic Website Compressor more than one site**

**A.** NO. You have to buy multiple license to use it more than one site. ( Number of license == Number of sites you can use on ).

**Q. I have trouble with wp supercache**

**A.** Please disable the `Compress pages so they’re served more quickly to visitors` feature in wp-supercache

## 6 - Contact

Feel free to contat me any time with your queries via mail abhimanyusharma003@gmail.com.
Make sure provide us full details about the script you are using on your site. ( Like wordpress - joomla - custom made script )

© Abhimanyu Sharma ( abhimanyu003 )