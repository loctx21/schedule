# Schedule
Help Fanpage moderator to create an exciting, engaging community by scheduling user generated content posting on Facebook in caring way.

## Motivation
When a Fan Page's audience grows to a certain size, fans will want to share their story with other fans by asking the Fan Page moderator to reshare their post, comment post and messages. 

To be able to create an exciting, engaging community the moderator will need to do these activities:
1. Share fan’s story to fanpage using their image, video, post’s content...
2. Comment on the new post to acknowledge the contribution from the content's owner. 
3. Reply to the original post, comment, conversation to let the content’s owner know that their content had been shared.

It’s huge time consuming to do all these activities using tools provided by Facebook when you receive over 50 pieces of content each day. This system was created as a tool to do all these activities semi-automatically.

## Requirements
1. PHP7+ for Laravel 5.8
2. Your Node package manager choice for ReactJs 16+
3. Openssl for your local ssl (Facebook now only work with https)
4. Facebook App (Please refer to Facebook document for setting up Facebook App)

## How to install
1. Pull the code to your system
2. Run ``composer install``
3. Run ``npm install``
4. Run normal Laravel setup
    - Key generate
    - Create .env
4. Run ``npm run watch`` to develop or ``npm run prod`` for preparing code for production
5. Configure https for your localhost
6. Configure Facebook App

## How to install with Docker
1. Insall Docker
2. Run composer install either by docker-composer image or your global composer
3. Create and self signed your own ssl certificate
4. Use ``nginx/conf.d/app.conf.example`` to create ``nginx/conf.d/app.conf`` with your information
5. Run ``docker-compose up -d`` to build the image and start container
6. Create your own database user and table on your ``db`` container
8. Configure your Facebook App information
9. Run normal Laravel setup on your ``app`` container
    - Key generate
    - Create .env

## Configuration you need to update in .env
- Facebook App Secret and ID
- Database (Use db for DB_HOST if you run with Docker)
- Other Laravel configuration

## Note
- ReactJs was setup using ``Laravel Mix``. 
- Javascript test runner: ``Jest``.
- Babel preset for testing ReactJs: ``react-app``.
- Javascript testing solution: ``React Testing Library`` https://testing-library.com/docs/react-testing-library/intro

