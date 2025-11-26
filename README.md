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


## Credits

Development by [Ghent Centre for Digital Humanities - Ghent University](https://www.ghentcdh.ugent.be/). Funded by the [GhentCDH research projects](https://www.ghentcdh.ugent.be/projects).

<img src="https://www.ghentcdh.ugent.be/ghentcdh_logo_blue_text_transparent_bg_landscape.svg" alt="Landscape" width="500">
