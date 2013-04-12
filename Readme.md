This is a fork of the Piwik plugin *IntranetSubNetwork* from
<http://dev.piwik.org/trac/ticket/1054>. Changes since that version can
be seen on <https://github.com/pklaus/IntranetSubNetwork/compare/v0.2...HEAD>.

### What this plugin does and how it works

I'm using the plugin to see how many visitors of my site were using IPv4
vs. IPv6. You can, however, use it to identify the number of users from
your local network (Intranet) or from any other subnet.  
This is done by assigning the network different names in the file
[IntranetSubNetwork.php][]
(see the [lines below #131][]).
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

#### Upgrading Considerations

If you had the original IntranetSubNetwork plugin (v0.2 by Alain) installed, you should
be carefull about upgrading because I [changed the name of the binary
archive blob](https://github.com/pklaus/IntranetSubNetwork/commit/98bc79f).
This means that you have to discard the archive tables in your Piwik
database when upgrading from this old version.

### Resources

* If all you're interested in is IPv6 vs IPv4 users, the plugin [IPv6Usage][]
  may also do the job for you.

[IntranetSubNetwork.php]: https://github.com/pklaus/IntranetSubNetwork/blob/master/IntranetSubNetwork.php
[lines below #131]: https://github.com/pklaus/IntranetSubNetwork/blob/master/IntranetSubNetwork.php#L131
[IPv6Usage]: https://github.com/halfdan/IPv6Usage
