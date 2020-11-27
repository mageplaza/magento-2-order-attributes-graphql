# Magento 2 Order Attributes GraphQL (Support PWA)

[Mageplaza Order Attributes for Magento 2](https://www.mageplaza.com/magento-2-order-attributes/) adds more essential attributes to orders from the checkout page that make it easier for the store owners to process and track the orders online. 

By adding necessary attributes to the checkout page with additional questions, the store owners can collect more information from customers to make their orders’ information more sufficient. This also enables them to process orders more quickly and accurately. When customers can get their purchases swiftly done, they will be more satisfied with what they buy, and the shopping experience increases as a result. 

Magento 2 Order Attributes supports multiple types of attributes or questions, including: 
- Text field
- Text area
- Date
- Yes/No
- Dropdown
- Multiple Select
- Single Select with image
- Multi-select with image
- Media image
- Single file attachment
- Content

The extension also assists the store owners to enable a specific attribute depending on a parent attribute. It means that the attribute only displays when the customers choose the parent attribute. For example, the attribute “Please select a preferred wrapping paper” only shows  when customers say “Yes” to the question “Would you like to use the gift wrap option.” Besides, the store admin can set the attribute display depending on the shipping method. 

The store admins can easily review the order attributes from the backend because they will be included in the Sales Order Grid. Additionally, the order attributes are added to different sections in the checkout page, such as Shipping Address, Shipping Method Top, Payment Method Bottom, and Order Summary. After logging in, customers can also see the order attributes via Order View Page.

Especially, **Magento 2 Order Attributes GraphQL is a part of the Mageplaza Order Attributes extension that adds GraphQL features, making it easier and faster for PWA compatibility.** 

## 1. How to install
Run the following command in Magento 2 root folder:

```
composer require mageplaza/module-order-attributes-graphql
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

**Note:** 
Magento 2 Order Attributes GraphQL requires installing [Mageplaza Order Attributes](https://www.mageplaza.com/magento-2-order-attributes/) in your Magento installation. 

## 2. How to use
Order Attributes GraphQL by Mageplaza enables you to view and collect order attribute information as well as save order attributes when your customers place orders via GraphQL. 

**Note**
To start using Order Attributes GraphQL, you need to:
- Use Magento 2.3.x. 
- Return your site to developer mode

## 3. Devdocs 
- [Magento 2 Order Attributes API & examples](https://documenter.getpostman.com/view/10589000/Szf5399x)
- [Magento 2 Order Attributes GraphQL & examples](https://documenter.getpostman.com/view/10589000/Szf539EJ)

Click on Run in Postman to add these collections to your workspace quickly. 

![Magento 2 blog graphql pwa](https://i.imgur.com/lhsXlUR.gif)

## 4. Contribute to this module

Fee free to **Fork** and contribute to this module. You can create a pull request, and we will consider to merge your changes in the main branch. 

## 5. Get support 

- Feel free to [contact us](https://www.mageplaza.com/contact.html) if you want to discuss more or have any questions. Our support team is always willing to hear your voices and resolve your problems. 
- If this post is helpful for you, don't hesitate to give it a **Star** ![star](https://i.imgur.com/S8e0ctO.png)

