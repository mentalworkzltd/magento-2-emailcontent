# Mentalworkz Email Content Magento 2.x Module

This module allows you to create and manage content that can be placed into emails via a directive. You can configure conditions that should be met in order for the content to be rendered within the email template.

## 1. Documentation

#### Email Content Management

You can access the email content section via:
1. Admin menu -> Content -> Elements -> Targeted Email Content
2. Admin menu -> Marketing -> Communications -> Targeted Email Content

From here you will see a grid list of any already defined email content. From here you can:

 - Add new email content
 - Edit existing content, both inline and by selecting the actions column 'Edit' option
 - Delete content using both the actions column 'Delete' option, and using the actions dropdown to delete multiple content
 - Update the status of content, both inline or using the actions droppown
 - Filter the grid
 
 <br />
 
 Upon adding a new content, or editing existing, you are presented with a form that allows the following:
 
 **General information**
 
 - Title - a descriptive title for the content
 - Identifier - an identifier to loow you to reference the content within the email directive
 - Email Template Directive - upon save, this will give you the directive you need to place in the relevant email template
 - Description - more information about the content, optional.
 - Sort Order - if multiple email content entities share an Identifier, this allows you to define the sort order the content should be displayed in
 - Is Active - enable or disable the content
 - Store View - the stores the content is applicable too
 
 <br />
 
 **Display Conditions**
 
 Configure conditions that should be met before the email content can be displayed.
 This is a custom field, similar to the sales rule attribute combination field, and allows you to set display conditions such as:
 
 - Date Range
 - Customer ID and Customer Group
 - Order Total Items Qty
 - Order Item Attribute Combination
 
 For the latter, you can select from several fields: **Product Name, Price, Sku**  
 You can add your configurable product attributes to this list, more about that explained below in the configuration section.
 
 For each condition you can apply the following condition operators where applicable:
 
 - Is
 - Is not 
 - Is one of 
 - Is not one of
 - Contains
 - Does not contain
 
 <br />
 
 **Email Content**
 
 This is where you define your actual email content using a wysiwyg editor.
 Above the wsiwyg you can opt to have your content wrapped in a table tag, with a max-width and padding. You can define generic table wrapper settings 
 for all email content in the system configuration (more on that below), or override those settings for specific pieces of content.
 
 Within the wysiwyg you can define content as you usually would within a wysiwyg editor, adding tables for layout, adding text and images, or adding any complex data via any widgets you have available.
 

<br />


#### Email Content System Configuration

- Enable/disable the module
- Enable debugging. If turned on, when email content is rendered within emails, debug logs are added stating the outcome of any display conditions
- Select configurable product attributes that should be available within the Display Conditions form field in the Email Content edit form
- Set the default content table wrapper settings displayed on Email Content add/edit pages.

<br />



## 2. Screenshots

![alt text](https://github.com/mentalworkz//blob/[branch]/image.jpg?raw=true)



## 3. How to install

#### Method 1: Install ready-to-paste package

Copy the module files to your site root **app/code/Mentalworkz/EmailContent** directory, then run the following commands:

```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```


#### Method 2: Install via composer (Recommend)

Run the following command in Magento 2 root folder

```
composer require mentalworkz/module-email-content
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```