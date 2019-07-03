## Client Share

The Client Share app is used to improve business results by strengthening and deepening account relationships between buyers and sellers across the globe.

Client Share is a secure, collaborative, digital community for Sales and Account Management, giving you and your client one place to share and access content, collaborate, and rate your business relationship anytime, anywhere. And our relationship analytics enable decisions to be made based on account insight not hindsight.

## About Project

- Language : PHP (7.2.*)
- Framework: Laravel (5.8.*)
- Database :  PostgreSql (9.6)
- Frontend library : React (16.8.5)  

## How to install project on local
  
   Open CLI and run following commands to set up at local:

 - Install `apache` webserver.

 - Install `git` CLI.

 - Install `php 7.2` and Laravel dependent php extension (eg. `php-common php-mbstring php-xml php-zip curl, gd, IMagick` etc)

 - Install `Redis` Refer to https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-redis-on-ubuntu-18-04 for more information.  

   - **Clone the project**
        >
            git clone https://github.com/uCreateit/clientshare-web.git

  - **Set permissions**
       >
            sudo chmod -R 777 { project-storage-path }
            sudo chmod -R 777 { project-bootstrap-path }

  - **Go to project directory**
       >
            cd clientshare-web

  - **Copy .env.example to .env**
       >
            cp .env.example .env

- **Install the dependencies**    
>
           composer install
  
- **Install the front-end dependencies**    
>
           npm install

# Database installation
- **How to install postgresql ( Ubuntu )**
    >
        sudo apt-get install postgresql postgresql-contrib
- **Which UI being used to connect to DB**
    >
        pgadmin
- **Create  database**
    >
         1. login to pgsql
          sudo psql -h localhost -U postgres    
          2. create database clientshare;
- **Update your .env file**
    >
       Update your DATABASE_URL with your local DB path like: DATABASE_URL=postgres://user_name:password@host_name:port/database_name.
       Update your REDIS_URL with your local Redis server path like: REDIS_URL=redis://user_name:password@host_name:port
- **Generate Key**
    >
    Run command: php artisan key:generate

# Post Installation steps
 - **Run database migrations**
    >
        php artisan migrate

- **Start server**
    >
        php artisan serve
        npm run dev
        The API and Web app will be running on localhost:8000 now

# Run project test cases
-  **Create separate database for testing**
    >
         1. login to pgsql
          sudo psql -h localhost -U postgres    
         2. create database clientshare_test;

- ** Update `phpunit.xml` file and Run tests.**
    >   
       1. Update DATABASE URL as   
       <env name="DATABASE_URL" value="postgres://user_name:password@host_name:port/clientshare_test"/>
       2. Run test cases. 
         Run command: vendor/phpunit/phpunit/phpunit.


# External Services/API Reference
- **Email Service**
    >
	 - PostMark
	 - Create Account on Postmark (https://postmarkapp.com) and verify the sender signatures.
	 - Set Postmark token in environment/config variables

- **Images Storage**
    >
        - AWS S3
         1. Your S3 credentials can be found on the Security Credentials section of AWS Account
         2. To create a bucket access the S3 section of the AWS Management Console
         3. Set AWS access key, secret key, bucket name etc. as environment variables.
        Reference: https://aws.amazon.com/s3

- **Embed.ly:**
   >
        It is used for getting the url metadata
        Reference: http://embed.ly

- **Linkedin API:**
   >
        It is used to get the user information from linkedin
        Reference: https://developer.linkedin.com

- **Clearbit API:**
   >
        It is used to get the company logo from clearbit api
        Reference: https://clearbit.com/docs#autocomplete-api

- **Mixpanel API:**
   >
        It is used to Log events performed by user
        Reference: https://developer.mixpanel.com/docs/php

- **Power BI API:**
   >
        It is used to create visually immersive, and interactive insights of data.
        Reference: https://docs.microsoft.com/en-us/power-bi/developer/embedding

- **SSL update steps:**
   >
        1. Go to godaddy => Download certificate
        2. Unzip the downloaded files
        3. Create new file with name server.crt by combining downloaded files using command: cat file1 file2 > server.crt
        4. Go to production APP
        5. Go to setting => SSL configure (Open popup)
        6. Upload the server.crt and server.key file (For server.key file developer can connect with CS team member)
        
        Generate private key and csr using command: (If needed)
        openssl genrsa -des3 -out <private key file name>.key 2048

        OR

        Update on heroku using command:
        1. run heroku certs:update server.crt server.key (if you have updated your certificates )


