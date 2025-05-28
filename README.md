# PHP_AES-CORS_API_Library
API using raw Curl command for free PHP, MySql hosting server. All Request like, GET, POST, PUT, DELETE example are Implement here. Its very easy to use. I make this library for smart and easy working

### How to use it:
  Just include a library on your PHP file. like,
  ```
    <?php
        include 'singletone.php';
    ?>
  ```

  Call the function fetch_it("here is your api url"). Its response your api value.
  ```
    <h2><?php echo fetch_it("http://nazmulalamshuvo.42web.io/dsell/restapi.php"); ?></h2>
  ```

  Output:
  <h2>{"Peter":35,"Ben":37,"Joe":43}</h2>
