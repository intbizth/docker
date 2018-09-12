
## Variables
**RTO = Remove to Off**

### Nginx
| RTO | Variable         | Value            | Note
|:---:|------------------|------------------|------
|  Y  | HOST_MAPs        | Strings          | Mapping host & port eg. `abc.com 8888; def.com 9999;`.
|  Y  | NO_PROXY         | 1                | No proxy (eg. cloudfare) over on this system.
|  Y  | SERVER_NAME      | String           | Set `server_name` to restrict access eg. `abc.def.com`.
|  N  | FPM_UPSTREAM_URL | String           | Set `fastcgi_pass` for `php-fpm` default to `php:9000`.
|  N  | SOCKET_IO_URL    | String           | Set `proxy_pass` for `websocket` default to `node:80`.

### Nodejs
| RTO | Variable         | Value            | Note
|:---:|------------------|------------------|------
|  N  | AMQP_HOST        | Strings          | Default to `amqp://guest:guest@localhost:5672`.
|  N  | AMQP_QUEUES      | Strings          | Default to `socket,worker`.

### PHP
| RTO | Variable         | Value            | Note
|:---:|------------------|------------------|------
|  Y  | DATA_SETUP       | 1                | Use for first time setup -- Create database.
|  Y  | RESTART          | 1                | Use for restart without run deploy script, Usually when `Scale Up` machine.
|  Y  | PHP_INIs         | Strings          | Multiple lines support eg. `PHP_INIs: >[Enter to new line]date.timezone=Asia/Bangok[Enter to new line]`.
|  Y  | FPM_CONFs        | Strings          | Multiple lines support.
|  N  | DEPLOYER_TASK    | Strings          | Deploying task eg. `deploy:unlock` default to `deploy`.
|  Y  | REPOSITORY       | Strings          | Github repository using ssh remote style: `https://github_access_token:@github.com/user/repo`
|  Y  | BRANCH           | Strings          | Github repo's branch to build default to `build`.
|  Y  | OPcode_ON        | Strings          | If `Opcode` cache available to clear.
|  Y  | APC_ON           | Strings          | If `Apc` cache available to clear.
|  Y  | APCu_ON          | Strings          | If `Apcu` cache available to clear.
|  Y  | CF_EMAIL         | Strings          | If `cloudfare` cache available to clear. Email value.
|  Y  | CF_DOMAIN        | Strings          | If `cloudfare` cache available to clear. Domain value.
|  Y  | CF_API_KEY       | Strings          | If `cloudfare` cache available to clear. API key value.
