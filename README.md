OpenJORT
========

Opening up Tunisia's Official Gazette

How to use these scripts :

1- First you need to download JORT PDF files from official website www.iort.gov.tn

Visit iort website and search publication by year, then copy paste and run the script getJORT.js in firebug 
The script will download Jort PDF files inside a webpage (10 at a time) Then jump to next page :)
Make sure you hit save download file location by default, to start download immediately instead of showing the download menu

2- Now you can convert PDF to text using the PDFtotext utility :
(if you don't have it run : $sudo apt-get install pdftotext)

$ for file in *.pdf; do pdftotext "$file" "$file.txt";done

3- Configure OpenJORT path variable to the location of your txt files converted without trailing slash in the end

// for example : 
$path = "annonces";

4- Then create a MySQL database with the structure below :


CREATE TABLE `announcements` (
  `id` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `gazette` int(10) NOT NULL,
  `json` text NOT NULL,
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `gazettes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `number` int(10) NOT NULL,
  `pubdate` date NOT NULL,
  `pubyear` int(4) NOT NULL,
  `pdflink` varchar(255) NOT NULL,
  `textlink` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


5-  Finally run the PHP script :

$ php OpenJORT.php

6- That's all !! Once the database is populated you can export data to any format you want using PHPMyAdmin for example. 

If you are curious to know how this script is written check this link (No sign-in required):
https://docs.google.com/document/d/1llQID6CbOClkBE1scpqPNasWlScpn-7AnJ3DmMrAwAQ/edit?usp=sharing

Feel free to fork, use and reuse !

- Hatem
http://hbyconsultancy.com