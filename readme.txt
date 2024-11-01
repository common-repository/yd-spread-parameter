=== YD Spread Parameter ===
Contributors: ydubois
Donate link: http://www.yann.com/
Tags: plugin, automatic, admin, administration, blogs, blog, sitewide, site-wide, replicate, centralize, parameter, centralized, management, French, English, spread, url, get
Requires at least: 2.9.1
Tested up to: 2.9.2
Stable tag: trunk

Tweaks URLs to keep and propagate a http get query parameter in all links site-wide ( like ?tpl=1 ).

== Description ==

= Spread your http get parameters on all your links! =

This plugin will automatically take care of "spreading" a parameter on all the links URL inside a WP or WP MU blog. 
It will add the given parameter at the end of any link URL inside the site.

It will also add the parameter as a hidden field to any form element contained in the page (such as the Wordress default search form);
if the form uses GET as a submit method, the URL parameter will be maintained.

This can be used to propagate identification tokens (for on-line shopping sites, etc.), 
or parameters related to the display 
(eg. used with the [WP Theme Switcher plugin](http://wordpress.org/extend/plugins/wp-theme-switcher/) to choose which theme should be used as a display template).

The plugin works perfectly with Wordpress MU, either deployed site-wide or on a single blog.

The plugin has its own settings page.

It is **fully internationalized**.

Base package includes .pot file for translation of the interface, and English and French versions.

= Active support =

Drop me a line on my [YD Spread Parameter plugin support site](http://www.yann.com/en/wp-plugins/yd-spread-parameter "Yann Dubois' Spread Parameter plugin for Wordpress") to report bugs, ask for a specific feature or improvement, or just tell me how you're using the plugin.

= Description en Français : =

Ce plug-in Wordpress permet de recopier automatiquement n'importe quel paramètre "get" http à la fin de tous les liens de votre site.

Le paramètre est également ajouté sous forme de champ caché à tous les formulaires présents dans la page (par exemple le formulaire de recherche par défaut de Wordpress)

Le plugin a sa propre page d'options dans l'administration.

Il est entièrement internationalisé.

La distribution standard inclut le fichier de traduction .pot et les versions française, et anglaise.

Le plugin peut fonctionner avec n'importe quelle langue ou jeu de caractères.

Pour toute aide ou information en français, laissez-moi un commentaire sur le [site de support du plugin YD Spread Parameter](http://www.yann.com/en/wp-plugins/yd-spread-parameter "Yann Dubois' Spread Parameter plugin for Wordpress").

= Funding Credits =

Original development of this plugin has been paid for by [Wellcom.fr](http://www.wellcom.fr "Wellcom"). Please visit their site!

Le développement d'origine de ce plugin a été financé par [Wellcom.fr](http://www.wellcom.fr "Wellcom"). Allez visiter leur site !

= Translation =

If you want to contribute to a translation of this plugin, please drop me a line by e-mail or leave a comment on the [plugin's page](http://www.yann.com/en/wp-plugins/yd-spread-parameter "Yann Dubois' Spread Parameter plugin for Wordpress").
You will get credit for your translation in the plugin file and this documentation, as well as a link on this page and on my developers' blog.

== Installation ==

1. Unzip yd-spread-parameter.zip
1. Upload the `yd-spread-parameter` directory and all its contents into the `/wp-content/plugins/` directory of your site
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the option admin page to select which parameters to spread site-wide in all your links.

For specific installations, some more information might be found on the [YD Spread Parameter plugin support page](http://www.yann.com/en/wp-plugins/yd-spread-parameter "Yann Dubois' Spread Parameter plugin for Wordpress")

== Frequently Asked Questions ==

= Where should I ask questions? =

http://www.yann.com/en/wp-plugins/yd-spread-parameter

Use comments.

I will answer only on that page so that all users can benefit from the answer. 
So please come back to see the answer or subscribe to that page's post comments.

= Puis-je poser des questions et avoir des docs en français ? =

Oui, l'auteur est français.
("but alors... you are French?")

== Screenshots ==

1. TODO.

== Plugin settingsd/options page ==

Use the plugin's own settings page to select which URL parameters to automatically replicate site-wide.

== Revisions ==

* 0.1.0 Original beta version.
* 0.2.0 Added support for forms (such as search form); optional sub-domain spreading.

== Changelog ==

= 0.1.0 =
* Initial release
= 0.2.0 =
Added support for forms (such as search form).
Spreading to subdomains is now an option.

== Upgrade Notice ==

= 0.1.0 =
Initial release.
= 0.2.0 =
No special instructions. See changelog for details.

== To Do ==

Test. Final release.

== Did you like it? ==

Drop me a line on http://www.yann.com/en/wp-plugins/yd-spread-parameter

And... *please* rate this plugin --&gt;