racktables_xenimport
=========

Import your Xen virtual machines into RackTables!

By Tyler Montgomery -  <tylerfixer@thecubed.com>

Requirements
---
 - cURL
 - PHP 5.3+
  - php-xmlrpc
  - php-xml
  - php-process
 - RackTables 0.20.6+


Installation
---
Simply clone the git url into your RackTables plugin folder!

```git clone https://github.com/thecubed/racktables_xenimport.git . ```

The plugin and it's dependencies will be copied into your RackTables plugins directory


Usage
---
Open the RackTables 'objects' screen, and you'll see a "Xen VM Import" tab.

### Import Types
 - **Mass Scan**

   This option allows you to scan all listed Xen servers for virtual machines using the same username and password for all servers. *Use this if you have one central username and password to log in and administer all Xen hosts*
   
   This field accepts hosts in the format like below:
   
        192.168.1.120
        192.168.1.220
        host.name.local
   
 - **Per-Server Scan**

   This option allows you to add individual Xen servers to the scan. Simply input the hostname or IP in the *Hostname/IP* box, and the username and password in their respective fields and click "Add".
   
   To remove a host from the list of hosts to scan, simply select it (or multiple with CTRL) and click "Remove"
   
 - **File Import**

   This option allows you to upload a json file created by the offline importer (not released yet) to the RackTables database. Use this option if your RackTables server and Xen hosts are not on the same network.
   
### Tag Options
Use the tag selectors to select what RackTables tags you would like applied to the new Virtual Machines and their respective VM Pools.

The author recommends a tag structure similar to the following:

        California Datacenter
            Network Devices
            Storage Devices
            Servers
                Virtual Machines
                VM Pools

This would allow you to put the newly discovered Virtual Machines into the "Virtual Machines" tag, and the new pools into the "VM Pools" tag. Searching for VMs or pools becomes much easier when your devices are properly tagged.

**Only ONE tag can be applied to each pool or imported machine.** Feel free to add more tags to your devices at a later time after the import is finished.

### Importing VMs and Pools

Once you have input your host information into the application, click "Process VMs". This will launch the scan process, which depending on the amount of servers you have specified can take up to 5 minutes to process.

After the scan is complete, you will be presented with the list of Virtual Machines and VM pools that were discovered during the probe.

Simply select which VMs you wish to have imported, and click "Import". This will import the VMs and Pools into your Racktables instance, and return you a list of the new items.

Handling Errors
---
The application is programmed to handle errors in two ways: *fatal errors*, and *recoverable errors*.

Fatal errors are ones that do not allow the appliction to proceed, and Recoverable errors allow for the scan of the next host or VM to continue, but the user should be notified of.

The only **fatal errors** are related to missing information in the initial scan menu, all other errors are recoverable.

To clear errors, simply click "Clear Errors" in the error container. This will erase your scan session and allow you to start fresh.

Credits
---
Special thanks to Andy Goodwin for his [PHP-XenAPI class](https://github.com/andygoodwin/PHP-xenapi).

The application uses a modified version of his library to support throwing exceptions and XenAPI commands with multiple underscores.

Contact Me
---
You can contact me via email at <tylerfixer@thecubed.com> or on irc.freenode.net as **IOMonster**.

---

Thanks!
