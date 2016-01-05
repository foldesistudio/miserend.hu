# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  config.vm.box = "scotch/box"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false
 
  #config.vm.network "forwarded_port", guest: 80, host: 8080  
   config.vm.network "private_network", ip: "192.168.33.10"
   config.ssh.forward_agent = true

   config.vm.provision "shell", inline: <<-SHELL
  
    ssh-add
    ssh-add -L | grep /miserend > ~/.ssh/miserend

     # setup hosts file
    VHOSTT=$(cat <<EOF
export MISEREND_WEBAPP_ENVIRONMENT=staging
export MYSQL_MISEREND_USER=root
export MYSQL_MISEREND_PASSWORD=root
export MYSQL_MISEREND_DATABASE=miserend
EOF
)
    echo "${VHOSTT}" > /etc/environment

    sudo apt-get update
    sudo apt-get -y install phpunit mc
      
    # setup hosts file
    VHOST=$(cat <<EOF
    <VirtualHost *:80>
        DocumentRoot "/vagrant"
        php_admin_value sendmail_path "/usr/bin/env catchmail -f test@miserend.hu --smtp-ip 0.0.0.0"
        <Directory "/vagrant/">
            SetEnv MISEREND_WEBAPP_ENVIRONMENT staging
            SetEnv MYSQL_MISEREND_USER root
            SetEnv MYSQL_MISEREND_PASSWORD root
            SetEnv MYSQL_MISEREND_DATABASE miserend
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
EOF
)
    echo "${VHOST}" > /etc/apache2/sites-available/000-default.conf
    ${VHOSTT}

    sudo a2enmod rewrite
    service apache2 restart

    source /etc/profile
    cd /vagrant
    npm install
 
    sudo composer self-update
    composer install

    echo "creating test and example databases..."
    echo "create database if not exists miserend character set utf8 collate utf8_unicode_ci;" | mysql -u root --password="root"
    echo "create database if not exists miserend_testing character set utf8 collate utf8_unicode_ci;" | mysql -u root --password="root"
    php install.php    
    export MYSQL_MISEREND_DATABASE=miserend_testing
    php install.php
    export MYSQL_MISEREND_DATABASE=miserend
    echo "downloading database from miserend.hu..."
    ssh download@miserend.hu -i ~/.ssh/miserend -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o PasswordAuthentication=no -o IdentitiesOnly=yes | mysql -u root -p'root' miserend
    echo ":)"
    
    mysqld
    
    /home/vagrant/.rbenv/shims/mailcatcher --http-ip=0.0.0.0
   SHELL
end
