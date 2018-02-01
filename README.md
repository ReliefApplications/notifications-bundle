Notifications Bundle
===================== 

## Installation 

### Step 1: Download the Bundle 

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require relief_applications/notifications-bundle
```

### Step 2: Enable the Bundle 

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new RA\NotificationsBundle\RANotificationsBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3 : Configure the Bundle 

In your project, add at the end of the file `app/config/config.yml` the following configuration :
```yml
ra_notifications:
    android:
        server_key: "%android_server_key%"
        fcm_server: "%android_fcm_server%"
    ios:
        push_passphrase: '%ios_push_passphrase%'
        push_certificate: 'null'
        apns_server: '%ios_apns_server%'
    device:
        class: YourBundle\Entity\Device
    contexts:
        ctx_1:
            ios:
                push_certificate: /var/ioskeys/crises.pem
                apns_topic: topic_name1
        ctx_2:
            ios:
                push_certificate: /var/ioskeys/americas.pem
                apns_topic: topic_name2

```

**Optional** (but recommended) **:**

For improved error management for iOS notifications, you will need http2 support for cURL on your machine. [See this tutorial.](https://serversforhackers.com/video/curl-with-http2-support)

Once you have followed this tutorial, check that cURL supports HTTP/2 by running :
```bash
$ curl --http2 -I https://nghttp2.org/
```

If it works, you can remove the line :
```yml
            protocol: legacy
```
in your `app/config/config.yml` file.

