# Testing imports using CollectiveAccess Web Services and PHPStorm REST Client

Recently PHPStorm has had an [editor based REST client](https://www.jetbrains.com/help/phpstorm/rest-client-in-phpstorm-code-editor.html).

Using this it's possible to retrieve data from the CA web services. Create a config file in your PHPStorm project:
 **rest-client.env.json** (suggestion - in the `config/` directory) with the following contents, updating the settings where necessary:
 ```json
 {
 	"development": {
 		"host": "localhost",
 		"path": "/csiro/providence",
 		"user": "administrator",
 		"password": "password"
 	}
 }
```
Once you have done that you can run a file such as [taxon.http](collectionTwo/taxon.http) to retrieve the 
