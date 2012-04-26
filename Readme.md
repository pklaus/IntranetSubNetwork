This is a fork of the Piwik plugin *IntranetSubNetwork* from
<http://dev.piwik.org/trac/ticket/1054>

### What this plugin does and how it works

I'm using the plugin to see how many visitors of my site were using IPv4
vs. IPv6. You can, however, use it to identify the number of users from
your local network (Intranet) or from any other subnet.  
This is done by assigning the network different names in the file
[IntranetSubNetwork.php][]
(see the [lines below #102][]).
Here is an example that lets Piwik assign the network label *Global
IPv4* to any IPv4 visitors:

```php
<?php
if (Piwik_IP::isIpInRange($visitorInfo['location_ip'], array('0.0.0.0/0'))) { $networkName = 'Global IPv4'; }
?>
````

### General Installation Instructions

1. Create the folder `./IntranetSubNetwork` in the plugins folder of your Piwik installation.  
   Then copy the plugin files into that folder.
2. (optional) Adopt the networks defined in [IntranetSubNetwork.php][] to your needs.
3. Activate the plugin on Piwik's settings page.
4. Add the *Visitor Networks* widget to your Piwik Dashboard.

#### Installation as a Git repository

If you know how to use [Git](http://git-scm.com/), I recommend to
install the plugin this way (makes it easier to keep it up to date):

```bash
cd /var/www/path/to/your/piwik/installation/plugins/
git clone git://github.com/pklaus/IntranetSubNetwork.git
```

[IntranetSubNetwork.php]: https://github.com/pklaus/IntranetSubNetwork/blob/master/IntranetSubNetwork.php
[lines below #102]: https://github.com/pklaus/IntranetSubNetwork/blob/master/IntranetSubNetwork.php#L102
