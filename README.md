# newrelic-rabbitmq-agent-monitoring-integration

https://docs.newrelic.com/docs/infrastructure/host-integrations/host-integrations-list/rabbitmq-monitoring-integration/

## Install 

1. Copy `.env.simple` -> `.env` file 
2. Set valid .env variables 
3. Build images `docker-compose build`
4. Up environment `docker-compose up -d`
5. Check current state for each containers ` docker-compose ps`

State must be Up 
Example: 

```
                         Name                                        Command               State                                             Ports                                           
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
newrelic-rabbitmq-agent-monitoring-integration_broker_1   docker-entrypoint.sh rabbi ...   Up      15671/tcp, 0.0.0.0:15672->15672/tcp, 25672/tcp, 4369/tcp, 5671/tcp, 0.0.0.0:5672->5672/tcp
newrelic-rabbitmq-agent-monitoring-integration_daemon_1   sh /usr/bin/docker-entrypo ...   Up      31339/tcp                                                                                 
newrelic-rabbitmq-agent-monitoring-integration_nri_1      ./entrypoint.sh /usr/bin/n ...   Up                                                                                                
newrelic-rabbitmq-agent-monitoring-integration_phpcli_1   docker-php-entrypoint sh         Up      0.0.0.0:88->80/tcp, 9000/tcp 

```

 6. Install packages.  Go into container `sh cli.sh` and run `composer install`

 


## Run 

### RabbitMQ check host 

http://loclalhost:15672 

Login and password from .env file 


### For run producer 

Go into container `sh cli.sh` and run next command 

```bash
php producer.php --url amqp://user:secret@broker/ --name import.test --delaytime 1000
```

delaytime - speed for send message into queue 
name - queue name for testing 
`amqp://user:secret@broker/` - connection string 
Login and password from .env file 


### Run consumer 

```bash 
    php consumer.php --url amqp://user:secret@broker/ --name import.test --delaytime 1000
```

delaytime - speed for send message into queue 
name - queue name for testing 
`amqp://user:secret@broker/` - connection string 
Login and password from .env file 
