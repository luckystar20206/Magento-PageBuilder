![Goomento Page Builder - Drag and Drop](https://i.imgur.com/pR0pLoA.png)

# Goomento **Page Builder**, a free drag and drop toolkit that supports to create custom pages, landing pages, blocks and store designs without writing a line of code

**Goomento Page Builder** is a *Free Magento 2 Page Builder Extension*, allows you to set up your own website in any industry easily by simple dragging and dropping manipulation. Notably, it can reuse your previous templates and sections to customize and redesign with your new creativity. All this process has an absence of coding involvement and configures instantly.

_This module is inspired by Elementor for WordPress let say Elementor for Magento, precisely, It's Goomento for Magento_

## 1. How to install Magento 2 Goomento Page Builder

### Install via composer (recommend)

Run the following command in Magento 2 root folder:

```
composer require goomento/module-page-builder
php bin/magento module:enable Goomento_PageBuilder
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```
For more functionalities (Eg: Slider, Banner, Audio, Products ...), check this out 
**[Goomento BuilderWidgets](https://github.com/Goomento/BuilderWidgets)** or run this command to install in short:

```
composer require goomento/module-builder-widgets
```

## 2. Configuration

To display Goomento Page Builder content on storefront, enable module in

    Stores > Settings > Configuration > Goomento > Page Builder > General > Active

Change to **Yes** to enable module on storefront.

## 3. Features

- Drag and Drop visual editor
- Section / Pages / Templates control
- Import / Export content
- Undo / Redo / Duplicate and more ...
- History / Revision management
- Reuse as page / template
- Widgets hub / management
- Adaptability in any store
- High speed optimize
- Add custom CSS

## 4. Design concept

#### Goomento Page Builder will overwrite the main content of these Magento entities:

- Cms Page
- Cms Block
- Catalog Product
- Catalog Category

#### For every buildable content, asset resource files (CSS, Javascript, images ... ) will be stored or downloaded 
Make sure this folder `pub/media/goomento`  is writeable for fully functional 

## 5. How to use

Check this [Wiki page](https://github.com/Goomento/PageBuilder/wiki/How-To-Use) for use

## 6. Personalize

**Goomento Page Builder** was designed to be extendable easily, including: 

- Widget (The piece of your page, Eg: Image, Text, Banner, Video, Audio ...)
- Control (The place where you define data type and behavior, Eg: Image selector, Text input field ...)

Check this [Wiki page](https://github.com/Goomento/PageBuilder/wiki/Personalize) for more detail

## 7. Troubleshoot

**The Page Builder did not display on storefront**

- Make sure that Goomento Page Builder module was enabled, click [here](https://github.com/Goomento/PageBuilder#2-configuration)
- Make sure Page Builder status is `Published` and `Store view` is matching with current storefront
- For Magento entity, (Eg: Product, Category ...) make sure `Page Builder Content` was selected and `Active` turned to `Yes`

**Visual editor did not load**

- Visual editor may crash for the first load, it's due to the timeout of loading resources from CDN,
try to reload your browser, It'll go away

Something else? Contact us: [store.goomento@gmail.com](mailto:store.goomento@gmail.com)

## 8. Version compatible

Magento: 2.3.x, 2.4.x

## 9. Changelog

What's news? See here [CHANGELOG.md](https://github.com/Goomento/PageBuilder/blob/master/CHANGELOG.md).

## 10. Open an issue and Contribution

Feel free to Open an Issue, Fork and Create Pull Request (PR) on GitHub

For opening an issue, click [here](https://github.com/Goomento/PageBuilder/issues/new).

## 11. Screenshots

![Goomento Page Builder Interface](https://i.imgur.com/hiRyX5Y.gif)
One click to use

![Goomento Page Builder History Management](https://i.imgur.com/cpxv7Kn.gif)
History/ Revision Management

![Goomento Page Builder Editing](https://i.imgur.com/rj10Ncs.gif)
Easy to use

![Goomento Page Builder Responsive](https://i.imgur.com/abT8OtO.gif)
Responsive control
