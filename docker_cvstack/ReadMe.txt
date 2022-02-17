HOW TO USE

*** before START please check docker ps first.

1. START :: docker-compose up -d
2. STOP :: docker-compose down
3. OPEN PORT :: Readme first


// IMPORT DATABASE
> docker exec -it docker_xxx_mysql57_1 bash
> root@a58828cc02f1:/# cd /var/lib/mysql
> root@a58828cc02f1:/var/lib/mysql# ls
auto.cnf  xxx__bu.sql  ca-key.pem  ca.pem  client-cert.pem  client-key.pem  ib_buffer_pool  ib_logfile0  ib_logfile1  ibdata1  ibtmp1  mysql  performance_schema  private_key.pem  public_key.pem  server-cert.pem  server-key.pem  sys
> root@a58828cc02f1:/var/lib/mysql# ls
auto.cnf  xxx  xxx__bu.sql  ca-key.pem  ca.pem  client-cert.pem  client-key.pem  ib_buffer_pool  ib_logfile0  ib_logfile1  ibdata1  ibtmp1  mysql  performance_schema  private_key.pem  public_key.pem  server-cert.pem  server-key.pem  sys
> root@a58828cc02f1:/var/lib/mysql# mysql -u root -p xxx < xxx__bu.sql
> exit // FOR QUIT THIS CONTAINER
