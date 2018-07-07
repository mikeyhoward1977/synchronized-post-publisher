=== Synchronized Post Publisher ===
Contributors: mikeyhoward1977
Tags: documentation, posts, publish, publish posts, mailchimp
Requires at least: 4.1
Tested up to: 4.9.7
Requires PHP: 5.4
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automate the publishing of multiple posts, pages, products and MailChimp campaigns at the same time.

== Description ==

Synchronized Post Publisher (SPP) for WordPress, provides you with the ability to add pages, posts (of any type), and MailChimp email campaigns together and publish them all simultaneously.

Let's say you're about to release a new product, or an update to an existing one. This product may need product page, a release post, some documentation and maybe even an email campaign within MailChimp. It may be a WooCommerce product, an Easy Digital Downloads download, or anything else.

SPP enables you to create all of the above in advance and publish them all at the same time with minimal effort. Create all your documentation and your release post and add them to an SPP group. Then, optionally add your MailChimp email campaign to the SPP group and when you're ready to publish your product, all these posts will be published at the same time and your MailChimp campaigns will be sent.

Sure, there are other ways to achieve this. For example, you could visit each post individually and publish it, or you could visit the edit post page for each post type and publish via Quick Edit, but it's just not as quick and seamless as the Synchronized Post Publisher for WordPress.

Works with all ecommerce plugins such as WooCommerce and Easy Digital Downloads.

**Follow this plugin on [GitHub](https://github.com/mikeyhoward1977/synchronized-post-publisher)**

**Languages**

Would you like to help translate the plugin into more languages? [Join the WP-Translations Community](https://translate.wordpress.org/projects/wp-plugins/synchronized-post-publisher).

== Installation ==

**Automated Installation**

1. Login to WordPress admin
1. Head to **Plugins** -> **Add New**
1. Enter Synchronized Post Publisher into the search field
1. Click to install the plugin
1. Activate the plugin
1. Refresh the page and go to **SPP Groups** -> **Settings** to configure

**Manual Installation**

Once you have downloaded the plugin zip file, follow these simple instructions to get going;

1. Go to the the Synchronized Post Publisher WordPress plugin page at [https://wordpress.org/plugins/synchronized-post-publisher/](https://wordpress.org/plugins/synchronized-post-publisher/) and click the **Download** button to download the zip file
1. Login to your WordPress administration screen and select the "Plugins" -> "Add New" from the menu
1. Select "Upload Plugin" from the top of the main page
1. Click "Choose File" and select the **synchronized-post-publisher.zip** file you downloaded
1. Click "Install Now"
1. Once installation has finished, select "Activate Plugin"

== Frequently Asked Questions ==

= Which post types are supported? =

SPP supports any public post type. Go to **SPP Groups** -> **Settings** to select which post types you want to enable.

= How do I create an SPP group? =

Head to the **SPP Groups** menu option and create a group in the same way you would create any post or page within WordPress. Give the group a short description if you wish

= How do I add a post to an SPP group? =

When editing a post, if it is enabled for SPP grouping, the **Publish Group** option is added within the **Publish** meta box. The current group (or **None**) is displayed. Click edit to reveal a dropdown list of groups to which the post can be added. Select the group and click the **OK** button. The post is immediately added to the group

= How do I connect to MailChimp? =

Open the **Settings** page from within the **SPP Groups** menu. Enter your MailChimp API key in the **MailChimp API Key** field and click **Save Settings**. SPP will tell you if the connection is successful.

= How do I schedule my MailChimp campaign to be sent when the SPP group's posts are published? =

After entering your MailChimp API key, click the **SPP Groups** menu item to reveal a list of your SPP groups. Click the list you want to add your campaign(s) to. All of your available MailChimp campaigns will be displayed within a table at the foot of the page. Locate the campaigns you want to send when this SPP groups posts are published and click the **Add** button. The campaign will move to the scheduled campaigns table and be removed from the available campaigns table.

= How do I remove a scheduled MailChimp campaign from an SPP group =

Follow the same steps for adding a campaign but instead of clicking **Add** on a campaign, click **remove**.

= When do grouped posts get published? =

When you click the **Publish** button for any post within a group, all other posts within the group will also be published. A handy confirmation is displayed to ensure you don't do this by mistake

= When do MailChimp campaigns get sent? =

Once the posts within the SPP group have been published, any MailChimp campaigns you have scheduled to be sent, will be sent. Note that the time it takes to actually send the campaigns via email depends on the MailChimp queue times.

= Which post statuses are supported? =

Posts in the 'auto-draft', 'draft' or 'pending' status can be added to an SPP group

= Why can't I see all of my MailChimp campaigns? =

Only campaigns that are ready to be sent (draft) are listed. Make sure your campaign is in **Draft*8 status.

= I still can't see my campaign =

SPP caches results from MailChimp for performance. Due to this recent changes to campaigns may not immediately be visible. When editing the SPP group, click on the **Force a refresh** link to force SPP to update its cache immediately.

= What happens if I delete an SPP group? =

Not much. All posts within the group will have their association removed when the group is removed from trash (permanently deleted)

= How do I see which posts and MailChimp email campaigns are within a group? =

Head to the **SPP Groups** menu option, click the name of the group and all posts within the group will be displayed.

Additionally, any posts or pages within a group will have the group specified within the post table.

= Where can I find support? =

Support is provided via the [WordPress.org support forums](https://wordpress.org/support/plugin/synchronized-post-publisher). Post your questions and we'll reply as soon as we can!

== Screenshots ==

1. SPP settings screen. Select which post types can be grouped

2. Creating a new SPP group

3. Adding a post to an SPP group

4. A posts SPP group can be easily identified

5. Posts within an SPP group are easily identified on the posts list screen

6. List of SPP groups

7. An SPP group page where posts can be reviewed, removed and all posts within the group can be published with one click!

8. Confirmation of how many posts were published

== Changelog ==

= 1.2 =

**DATE**

* **New**: You can now schedule MailChimp email campaigns to be sent when an SPP Groups posts are published. Enter your MailChimp API key within *SPP Groups* -> *Settings* to get started

* **Tweak**: Incremented *Tested up to* WordPress version

= 1.1.1 =

**Monday, 21st May 2018**

**Tweak**: Tested up to WordPress 4.9.6

= 1.1 =

**Monday, 23rd April 2018**

* **New**: Added support for the `future` and `private` post statuses
* **Fix**: Corrected colspan value within the SPP group post list
* **Fix**: Settings updated notice was not being displayed when SPP settings were saved
* **Fix**: *Delete Groups on Publish* setting was not honored if a single post existed in the group and it was publised from the post page
* **Tweak**: Added request for WordPress.org rating
* **Tweak**: Spelling correction within description
* **Tweak**: Updated code comments
* **Dev**: Added hooks during SPP post publishing process - `wp_spp_before_publish_group_posts`, `wp_spp_publish_group_posts`, `wp_spp_before_publish_group_post`, `wp_spp_publish_group_post`

= 1.0 =

**Wednesday, 18th April 2018**

The initial release... Enjoy :)

== Upgrade Notice ==
