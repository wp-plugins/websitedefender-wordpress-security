<?php
/*
 * Displays the WP scan reports glossary
 *
 * @package ACX
 * @since v0.1
 */
?>
<?php
    //@@ require a valid request
if (!defined('ACX_PLUGIN_NAME')) { exit; }
    //@@ Only load in the plug-in pages
if (!ACX_SHOULD_LOAD) { exit; }
?>

<div class="metabox-holder">
    <div class="postbox">
        <h3 class="hndle"><span><?php echo __('Glossary');?></span></h3>
        <div class="inside acx-section-box">
            <p id="wsdwp_sql_mode"><?php echo __('<strong>SQL Mode</strong> (sql_mode) is a MySQL system variable. By means of this variable the MySQL Server SQL Mode is controlled. Many operational characteristics of MySQL Server can be configured by setting the SQL Mode. By setting the SQL Mode appropriately, a client program can instruct the server how strict or forgiving to be about accepting input data, enable or disable behaviors relating to standard SQL conformance, or provide better compatibility with other database systems. By default, the server uses a sql_mode value of  \' \'  (the empty string), which enables no restrictions. Thus, the server operates in forgiving mode (non-strict mode) by default. In non-strict mode, the MySQL server converts erroneous input values to the closest legal values (as determined from column definitions) and continues on its way.');?></p>
            <p id="wsdwp_safe_mode"><?php echo __('The PHP <strong>Safe Mode</strong> (safe_mode) is an attempt to solve the shared-server security problem. It is architecturally incorrect to try to solve this problem at the PHP level, but since the alternatives at the web server and OS levels aren\'t very realistic, many people, especially ISP\'s, use safe mode for now.');?></p>
            <p id="wsdwp_url_fopen"><?php echo __('PHP <strong>allow_url_fopen</strong> option, if enabled (allows PHP\'s file functions - such as \'file_get_contents()\' and the \'include\' and \'require\' statements), can retrieve data from remote locations, like an FTP or web site, which may pose a security risk.');?></p>
            <p id="wsdwp_memory_limit"><?php echo __('PHP <strong>memory_limit</strong> option sets the maximum amount of memory in bytes that a script is allowed to allocate. By enabling a realistic memory_limit you can protect your applications from certain types of Denial of Service attacks, and also from bugs in applications (such as infinite loops, poor use of image based functions, or other memory intensive mistakes).');?></p>
            <p id="wsdwp_upload_max_filesize"><?php echo __('PHP <strong>upload_max_filesize</strong> option limits the maximum size of files that PHP will accept through uploads. Attackers may attempt to send grossly oversized files to exhaust your system resources; by setting a realistic value here you can mitigate some of the damage by those attacks.');?></p>
            <p id="wsdwp_post_max_size"><?php echo __('PHP <strong>post_max_size</strong> option limits the maximum size of the POST request that PHP will process. Attackers may attempt to send grossly oversized POST requests to exhaust your system resources; by setting a realistic value here you can mitigate some of the damage by those attacks.');?></p>
            <p id="wsdwp_max_execution_time"><?php echo __('PHP <strong>max_execution_time</strong> option sets the maximum time in seconds a script is allowed to run before it is terminated by the parser. This helps prevent poorly written scripts from tying up the server.');?></p>
            <p id="wsdwp_exif"><?php echo __('PHP <strong>exif</strong> extension enables you to work with image meta data. For example, you may use exif functions to read meta data of pictures taken from digital cameras by working with information stored in the headers of the JPEG and TIFF images.');?></p>
            <p id="wsdwp_iptc"><?php echo __('<strong>IPTC</strong> data is a method of storing textual information in images defined by the International Press Telecommunications Council. It was developed for press photographers who need to attach information to images when they are submitting them electronically but it is useful for all photographers. It provides a standard way of storing information such as captions, keywords, location. Because the information is stored in the image in a standard way this information can be accessed by other IPTC aware applications.');?></p>
            <p id="wsdwp_xml"><?php echo __('<strong>XML</strong> (eXtensible Markup Language) is a data format for structured document interchange on the Web. It is a standard defined by the World Wide Web Consortium (W3C).');?></p>
        </div>
    </div>
</div>
