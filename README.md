# AuthCAS (module for Omeka-S)

This module enables CAS authentication for Omeka-S. 

This module requires the CAS service to return an e-mail address, which is used to identiy the exising Omeka-S user. 
If needed, you can configure the module to create a new user on first logon.

When enabled, the default authentication page is replaced with a redirect to a CAS login service. 

To disable this behaviour and access the Omeka authentication page, use '/login?omeka' to login.

## Requirements

- Omeka-S >= 4

## License

This module is published under the [MIT](LICENSE) license.

## Copyright

* Copyright [Ghent Centre for Digital Humanities](https://www.ghentcdh.ugent.be), 2024