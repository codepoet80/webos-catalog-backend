<p style="clear:both;margin-top:60px;" class="legal">
<a href="http://webosarchive.org">webOS Archive</a> provides this listing without profit and under Fair Use provisions as a digital archive for historical purposes.
<?php
    if (isset($config['contact_email']) && !empty($config['contact_email'])) {
      echo "If you have questions or comments, or to submit a take down request for IP you own the rights to, please <a href=\"javascript:document.location=atob('" . base64_encode("mailto:" . $config['contact_email'] . "?subject=webOS App Museum") . "')\">email the curator</a>!";
    }
  ?>
</p>
