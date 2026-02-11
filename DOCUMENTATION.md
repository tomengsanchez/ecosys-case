# Ecosys Profile Manager - Complete Documentation

## Table of Contents
1. [Overview](#overview)
2. [Plugin Architecture](#plugin-architecture)
3. [Custom Post Types](#custom-post-types)
4. [Core Components](#core-components)
5. [Admin Functionality](#admin-functionality)
6. [Public Functionality](#public-functionality)
7. [Hooks & Filters](#hooks--filters)
8. [Database Structure](#database-structure)
9. [API Reference](#api-reference)
10. [Installation & Activation](#installation--activation)

---

## Overview

**Plugin Name:** Ecosys Profile Manager  
**Version:** 1.0.0  
**Author:** Ecosys  
**Author URI:** https://ecosys.io  
**License:** GPL-2.0+  
**Minimum WordPress Version:** 5.0  
**Minimum PHP Version:** 7.2  
**Text Domain:** ecosys-profile-manager  

### Description
A comprehensive profile management plugin for WordPress that enables administrators to manage user profiles, projects, and organizational structures. The plugin provides a complete system for creating and managing profiles linked to projects with detailed structure information including multimedia support.

### Key Features
- Custom Profile post type for managing individual profiles
- Custom Project post type for organizing profiles into projects
- Custom Profile Structure post type for managing detailed structure information linked to profiles
- AJAX-powered modal dialogs for adding and editing structures
- Image support for structures with media uploader integration
- Advanced admin filtering and searching capabilities
- Internationalization support with 'ecosys-profile-manager' text domain

---

## Plugin Architecture

### File Structure
```
ecosys-profile-manager/
├── ecosys-profile-manager.php              # Main plugin file
├── README.md                               # Readme file
├── admin/
│   ├── class-ecosys-profile-manager-admin.php
│   ├── class-ecosys-profile-manager-profile-admin.php
│   ├── class-ecosys-profile-manager-project-admin.php
│   └── class-ecosys-profile-manager-structure-admin.php
├── includes/
│   ├── class-ecosys-profile-manager.php
│   ├── class-ecosys-profile-manager-activator.php
│   ├── class-ecosys-profile-manager-deactivator.php
│   ├── class-ecosys-profile-manager-cpt.php
│   ├── class-ecosys-profile-manager-i18n.php
│   ├── class-ecosys-profile-manager-loader.php
│   └── class-ecosys-profile-manager-public.php
└── assets/
    ├── css/
    │   ├── ecosys-profile-manager-admin.css
    │   └── ecosys-profile-manager-public.css
    └── js/
        ├── ecosys-profile-manager-admin.js
        └── ecosys-profile-manager-public.js
```

### Plugin Flow
1. **Initialization** - `ecosys-profile-manager.php` defines plugin metadata and loads core class
2. **Setup** - `Ecosys_Profile_Manager` class initializes dependencies and hooks
3. **Registration** - Custom post types and metaboxes are registered
4. **Execution** - Loader class registers all actions and filters with WordPress

---

## Custom Post Types

### 1. Profile (`profile`)

**Purpose:** Represents an individual profile entry with personal information.

**Slug:** `/profile/`  
**Menu Position:** 5  
**Menu Icon:** `dashicons-businessman`  
**Supports:** Custom fields  
**Capabilities:** Standard post capabilities

**Metaboxes:**
- **Profile Information** - Main profile data entry form
- **Structure Information** - Lists linked structures with add/edit/delete functionality

**Custom Fields (Meta Keys):**
- `_profile_name` (string) - Full name of the profile
- `_profile_contact_number` (string) - Contact phone number
- `_profile_age` (string) - Age of the individual
- `_profile_sex` (string) - Sex/Gender (male/female)
- `_profile_project_id` (int) - Associated Project post ID

**Admin Features:**
- Custom list columns for Profiles
- Project filter dropdown on admin list
- Search by profile name
- Filter by project

---

### 2. Project (`project`)

**Purpose:** Represents a project that groups multiple profiles together.

**Slug:** `/project/`  
**Menu Position:** 6  
**Menu Icon:** `dashicons-briefcase`  
**Supports:** Custom fields, Author  
**Capabilities:** Standard post capabilities

**Metaboxes:**
- **Project Information** - Project data entry form

**Custom Fields (Meta Keys):**
- `_project_name` (string) - Name of the project

**Admin Features:**
- Custom list columns showing project name
- Search by project name (searches both post title and meta)
- Direct link to edit from list view

---

### 3. Profile Structure (`profile_structure`)

**Purpose:** Stores detailed structure information linked to specific profiles, including images and descriptions.

**Slug:** `/profile-structure/`  
**Menu Position:** Nested under Profiles menu  
**Menu Path:** Edit Profile > Structures submenu  
**Supports:** Custom fields  
**Capabilities:** Standard post capabilities

**Metaboxes:**
- **Structure Information** - Structure data entry and image upload form

**Custom Fields (Meta Keys):**
- `_structure_profile_id` (int) - Associated Profile post ID
- `_structure_tag` (string) - Structure identifier/tag
- `_structure_pictures` (array) - Array of attachment IDs
- `_structure_description` (string) - Detailed description of structure

**Admin Features:**
- Profile selector dropdown
- Image/picture uploader with media library integration
- Custom list columns displaying structure information

---

## Core Components

### 1. Ecosys_Profile_Manager (Main Core Class)

**File:** `includes/class-ecosys-profile-manager.php`

**Responsibility:** Orchestrates the entire plugin initialization and WordPress integration.

**Key Properties:**
```php
protected $loader;              // Ecosys_Profile_Manager_Loader instance
protected $plugin_name;         // 'ecosys-profile-manager'
protected $version;             // Current plugin version from constant
```

**Key Methods:**

#### `__construct()`
Initializes the plugin, loads dependencies, sets locale, and registers all hooks.

```php
public function __construct() {
    $this->plugin_name = 'ecosys-profile-manager';
    $this->version     = ECOSYS_PROFILE_MANAGER_VERSION;
    
    $this->load_dependencies();    // Load all required classes
    $this->set_locale();           // Set up i18n
    $this->define_cpt_hooks();     // Register CPT hooks
    $this->define_admin_hooks();   // Register admin hooks
    $this->define_public_hooks();  // Register public hooks
}
```

#### `load_dependencies()`
Requires all necessary class files and initializes the loader.

**Loaded Classes:**
- `Ecosys_Profile_Manager_Loader`
- `Ecosys_Profile_Manager_i18n`
- `Ecosys_Profile_Manager_Admin`
- `Ecosys_Profile_Manager_Profile_Admin`
- `Ecosys_Profile_Manager_Project_Admin`
- `Ecosys_Profile_Manager_Structure_Admin`
- `Ecosys_Profile_Manager_Public`
- `Ecosys_Profile_Manager_CPT`

#### `set_locale()`
Sets up the internationalization text domain loading on `plugins_loaded` hook.

#### `define_cpt_hooks()`
Registers custom post type registration on WordPress `init` hook.

```php
$this->loader->add_action('init', $plugin_cpt, 'register_custom_post_types');
```

#### `define_admin_hooks()`
Registers all admin-related hooks including:
- Admin asset enqueuing (styles/scripts)
- Profile metabox registration and saving
- Structure information metabox with AJAX
- Structure admin metabox registration
- Project admin metabox registration
- Custom column management
- Search and filter functionality

#### `define_public_hooks()`
Registers public-facing asset enqueuing (styles/scripts).

#### `run()`
Executes the loader to register all collected hooks with WordPress.

```php
public function run() {
    $this->loader->run();
}
```

#### `get_plugin_name()` 
Returns: `'ecosys-profile-manager'`

#### `get_loader()`
Returns: The `Ecosys_Profile_Manager_Loader` instance

#### `get_version()`
Returns: Current plugin version string

---

### 2. Ecosys_Profile_Manager_Loader

**File:** `includes/class-ecosys-profile-manager-loader.php`

**Responsibility:** Manages and registers all WordPress hooks (actions and filters) in a centralized location.

**Key Properties:**
```php
protected $actions;    // Array of registered actions
protected $filters;    // Array of registered filters
```

**Key Methods:**

#### `add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)`
Adds an action to the actions collection.

**Parameters:**
- `$hook` (string) - WordPress action hook name
- `$component` (object) - Object instance containing the callback method
- `$callback` (string) - Method name to call
- `$priority` (int) - Hook priority (default: 10)
- `$accepted_args` (int) - Number of arguments passed to callback (default: 1)

#### `add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)`
Adds a filter to the filters collection.

**Parameters:** Same as `add_action()`

#### `run()`
Executes all registered hooks with WordPress using internal register methods.

**Internal Method:**
#### `add($hooks, $hook, $component, $callback, $priority, $accepted_args)`
Utility method that creates hook array and adds to collection.

---

### 3. Ecosys_Profile_Manager_CPT

**File:** `includes/class-ecosys-profile-manager-cpt.php`

**Responsibility:** Registers all custom post types for the plugin.

**Key Methods:**

#### `register_custom_post_types()`
Calls all individual post type registration methods.

#### `register_profile_post_type()`
Registers the Profile custom post type with labels, description, and capabilities.

**Arguments:**
```php
array(
    'labels'             => [...],                 // User-facing labels
    'description'        => 'Custom profile post type',
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array('slug' => 'profile'),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => 5,
    'menu_icon'          => 'dashicons-businessman',
    'supports'           => array('custom-fields')
)
```

#### `register_project_post_type()`
Registers the Project custom post type.

**Arguments:**
```php
array(
    'labels'             => [...],
    'description'        => 'Custom project post type',
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array('slug' => 'project'),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => 6,
    'menu_icon'          => 'dashicons-briefcase',
    'supports'           => array('custom-fields', 'author')
)
```

#### `register_profile_structure_post_type()`
Registers the Profile Structure custom post type.

**Arguments:**
```php
array(
    'labels'             => [...],
    'description'        => 'Structure information linked to profiles',
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => 'edit.php?post_type=profile',  // Submenu under Profiles
    'query_var'          => true,
    'rewrite'            => array('slug' => 'profile-structure'),
    'capability_type'    => 'post',
    'has_archive'        => false,
    'hierarchical'       => false,
    'supports'           => array('custom-fields')
)
```

---

### 4. Ecosys_Profile_Manager_i18n

**File:** `includes/class-ecosys-profile-manager-i18n.php`

**Responsibility:** Handles internationalization (i18n) and localization of the plugin.

**Key Methods:**

#### `load_plugin_textdomain()`
Loads the plugin's translation files from the `/languages` directory.

**Text Domain:** `ecosys-profile-manager`  
**Domain Path:** `/languages`

---

### 5. Ecosys_Profile_Manager_Activator/Deactivator

**Files:** 
- `includes/class-ecosys-profile-manager-activator.php`
- `includes/class-ecosys-profile-manager-deactivator.php`

**Responsibility:** Handle plugin activation and deactivation hooks.

**Activation:** Currently no specific database changes required (CPTs are registered dynamically)  
**Deactivation:** Currently no cleanup required

---

## Admin Functionality

### 1. Ecosys_Profile_Manager_Admin

**File:** `admin/class-ecosys-profile-manager-admin.php`

**Responsibility:** Base admin class handling global admin asset enqueuing.

**Key Methods:**

#### `enqueue_styles()`
Enqueues the admin stylesheet.

```
Handle: ecosys-profile-manager
File: assets/css/ecosys-profile-manager-admin.css
Version: Current plugin version
```

#### `enqueue_scripts()`
Enqueues admin JavaScript and media uploader.

```
Handle: ecosys-profile-manager
File: assets/js/ecosys-profile-manager-admin.js
Dependencies: jquery
Version: Current plugin version
Media: wp_enqueue_media() for media uploader access
```

---

### 2. Ecosys_Profile_Manager_Profile_Admin

**File:** `admin/class-ecosys-profile-manager-profile-admin.php`

**Responsibility:** Manages all Profile post type admin functionality, metaboxes, and AJAX handlers.

**Metabox Registration:**

#### `add_profile_metabox()`
Registers the main profile information metabox.

```php
add_meta_box(
    'profile_custom_fields',
    __('Profile Information', 'ecosys-profile-manager'),
    [$this, 'render_profile_metabox'],
    'profile',      // post type
    'normal',       // context
    'high'          // priority
);
```

#### `render_profile_metabox($post)`
Renders the profile metabox form with the following fields:

**Form Fields:**
1. **Profile Name** - Text input
2. **Contact Number** - Number input
3. **Age** - Number input (min: 0, max: 150)
4. **Sex** - Dropdown (Male/Female)
5. **Project** - Dropdown (Lists all projects)

**Security:** Includes WordPress nonce field for CSRF protection

#### `save_profile_metabox($post_id)`
Saves all profile metabox data with sanitation:

**Saved Meta Keys:**
- `_profile_name` - via `sanitize_text_field()`
- `_profile_contact_number` - via `sanitize_text_field()`
- `_profile_age` - via `sanitize_text_field()`
- `_profile_sex` - via `sanitize_text_field()`
- `_profile_project_id` - via `absint()`

**Security Checks:**
- Nonce verification
- User capability check (`edit_post`)

**Structure Information Metabox:**

#### `add_structure_information_metabox()`
Registers the structure information metabox showing linked structures.

```php
add_meta_box(
    'structure_information',
    __('Structure Information', 'ecosys-profile-manager'),
    [$this, 'render_structure_information_metabox'],
    'profile',
    'normal',
    'high'
);
```

#### `render_structure_information_metabox($post)`
Renders the structure management interface including:

**Features:**
- Warning message if profile is new (not yet saved)
- "Add Structure" button (disabled for new profiles)
- Structures table listing all linked structures
- Modal dialog for adding/editing structures

**Table Columns:**
- Structure Tag
- Images (thumbnails)
- Description
- Actions (Edit/Delete buttons)

**Modal Dialog:**
The modal includes fields for:
- Structure Tag (text input)
- Images (media uploader with preview)
- Description (textarea)

**Admin Filtering & Searching:**

#### `manage_profile_columns($columns)`
Customizes the profile list table columns.

#### `populate_profile_columns($column, $post_id)`
Populates custom columns with data.

#### `add_project_filter()`
Adds a project filter dropdown to the profile admin list.

#### `filter_by_project($query)`
Filters profiles by selected project via query modification.

#### `search_profile_by_name($search, $query)`
Makes profiles searchable by name (searches `_profile_name` meta field).

**AJAX Handlers:**

#### `ajax_add_structure()`
AJAX endpoint: `wp_ajax_ecosys_add_structure`

Handles adding new structures to a profile.

**Parameters (POST):**
- `structure_tag` - Structure identifier
- `structure_pictures` - Array of image attachment IDs
- `structure_description` - Structure description
- `profile_id` - Profile post ID

#### `ajax_get_structure()`
AJAX endpoint: `wp_ajax_ecosys_get_structure`

Retrieves structure data for editing.

**Parameters (POST):**
- `structure_id` - Structure post ID

#### `ajax_update_structure()`
AJAX endpoint: `wp_ajax_ecosys_update_structure`

Updates existing structure data.

**Parameters (POST):**
- `structure_id` - Structure post ID
- `structure_tag` - Updated tag
- `structure_pictures` - Updated image array
- `structure_description` - Updated description

---

### 3. Ecosys_Profile_Manager_Project_Admin

**File:** `admin/class-ecosys-profile-manager-project-admin.php`

**Responsibility:** Manages all Project post type admin functionality.

#### `add_project_metabox()`
Registers the project information metabox.

```php
add_meta_box(
    'project_custom_fields',
    __('Project Information', 'ecosys-profile-manager'),
    [$this, 'render_project_metabox'],
    'project',
    'normal',
    'high'
);
```

#### `render_project_metabox($post)`
Renders the project form with:

**Form Fields:**
1. **Project Name** - Text input (saved to `_project_name` meta)

#### `save_project_metabox($post_id)`
Saves project data:

**Saved Meta Keys:**
- `_project_name` - via `sanitize_text_field()`

**Security Checks:**
- Nonce verification
- User capability check

**Admin Columns:**

#### `manage_project_columns($columns)`
Customizes project list columns:
- Removes WordPress title column
- Adds custom project_name column as primary

#### `populate_project_columns($column, $post_id)`
Populates the project_name column with linked edit URL.

**Searching:**

#### `search_project_by_name($search, $query)`
Enables searching projects by name.

**Search Scope:**
- Post title
- `_project_name` meta field

**SQL Query:** Custom meta query using `$wpdb->prepare()` for security

---

### 4. Ecosys_Profile_Manager_Structure_Admin

**File:** `admin/class-ecosys-profile-manager-structure-admin.php`

**Responsibility:** Manages all Profile Structure post type admin functionality.

#### `add_structure_metabox()`
Registers the structure information metabox.

```php
add_meta_box(
    'structure_fields',
    __('Structure Information', 'ecosys-profile-manager'),
    [$this, 'render_structure_metabox'],
    'profile_structure',
    'normal',
    'high'
);
```

#### `render_structure_metabox($post)`
Renders the structure form with:

**Form Fields:**
1. **Profile** - Dropdown selector (all profiles)
2. **Structure Tag** - Text input
3. **Pictures** - Media uploader (multiple images)
4. **Description** - Textarea

**Features:**
- Pre-selects profile if coming from profile's Add Structure button (via URL parameter)
- Displays image preview/gallery
- Media library integration

**Data Retrieval:**
```php
$structure_tag        = get_post_meta($post->ID, '_structure_tag', true);
$structure_pictures   = get_post_meta($post->ID, '_structure_pictures', true);
$structure_description = get_post_meta($post->ID, '_structure_description', true);
$structure_profile_id = get_post_meta($post->ID, '_structure_profile_id', true);
```

#### `save_structure_metabox($post_id)`
Saves all structure data:

**Saved Meta Keys:**
- `_structure_profile_id` - via `absint()`
- `_structure_tag` - via `sanitize_text_field()`
- `_structure_pictures` - Array of attachment IDs
- `_structure_description` - via `wp_kses_post()`

**Security Checks:**
- Nonce verification
- User capability check

**Admin Columns:**

#### `manage_structure_columns($columns)`
Customizes structure list table columns.

#### `populate_structure_columns($column, $post_id)`
Populates custom structure columns with relevant data.

---

## Public Functionality

### Ecosys_Profile_Manager_Public

**File:** `includes/class-ecosys-profile-manager-public.php`

**Responsibility:** Handles public-facing (front-end) functionality.

#### `enqueue_styles()`
Enqueues public stylesheet.

```
Handle: ecosys-profile-manager
File: assets/css/ecosys-profile-manager-public.css
Version: Current plugin version
```

#### `enqueue_scripts()`
Enqueues public JavaScript.

```
Handle: ecosys-profile-manager
File: assets/js/ecosys-profile-manager-public.js
Dependencies: jquery
Version: Current plugin version
```

**Note:** Current public functionality is minimal. Extend this class to add front-end profile display, archives, or custom templates.

---

## Hooks & Filters

### WordPress Actions Used

**Plugin Initialization:**
- `register_activation_hook` - Calls `activate_ecosys_profile_manager()`
- `register_deactivation_hook` - Calls `deactivate_ecosys_profile_manager()`
- `plugins_loaded` - Loads plugin text domain

**Admin Hooks:**
- `admin_enqueue_scripts` - Enqueues admin styles and scripts
- `add_meta_boxes` - Registers all metaboxes
- `save_post` - Saves all metabox data
- `manage_*.php_custom_column` - Populates custom columns
- `restrict_manage_posts` - Adds filter dropdowns to admin list
- `wp_ajax_*` - AJAX endpoints for structure management
- `pre_get_posts` - Filters profile list by project
- `init` - Registers custom post types

**Public Hooks:**
- `wp_enqueue_scripts` - Enqueues public styles and scripts

### WordPress Filters Used

- `manage_profile_posts_columns` - Customizes profile list columns
- `manage_project_posts_columns` - Customizes project list columns
- `manage_profile_structure_posts_columns` - Customizes structure list columns
- `posts_search` - Custom search for profiles and projects
- `posts_{$post_type}_search` - Post type specific search modifications

### Custom Hooks (Not Implemented)

No custom hooks are currently defined. Consider adding:
- `ecosys_profile_saved` - After profile is saved
- `ecosys_structure_added` - After structure is added
- `ecosys_project_updated` - After project is updated

---

## Database Structure

### Post Meta Keys

**For Profile Posts:**
```
_profile_name           (string)  - Full name
_profile_contact_number (string)  - Phone/contact
_profile_age            (string)  - Age value
_profile_sex            (string)  - male/female
_profile_project_id     (integer) - Associated project ID
```

**For Project Posts:**
```
_project_name           (string)  - Project name/title
```

**For Profile Structure Posts:**
```
_structure_profile_id   (integer) - Associated profile ID
_structure_tag          (string)  - Structure identifier
_structure_pictures     (array)   - Attachment IDs
_structure_description  (string)  - HTML content description
```

### No Custom Tables

The plugin uses only WordPress post types and post meta (wp_posts and wp_postmeta tables). No custom database tables are created.

---

## API Reference

### Getting Profile Data

```php
// Get profile by ID
$profile_id = 123;
$profile = get_post($profile_id);

// Get profile meta
$name = get_post_meta($profile_id, '_profile_name', true);
$age = get_post_meta($profile_id, '_profile_age', true);
$sex = get_post_meta($profile_id, '_profile_sex', true);
$contact = get_post_meta($profile_id, '_profile_contact_number', true);
$project_id = get_post_meta($profile_id, '_profile_project_id', true);
```

### Querying Profiles

```php
// Get all profiles
$profiles = get_posts(array(
    'post_type'      => 'profile',
    'posts_per_page' => -1,
));

// Get profiles for a specific project
$project_id = 456;
$profiles = get_posts(array(
    'post_type'      => 'profile',
    'posts_per_page' => -1,
    'meta_key'       => '_profile_project_id',
    'meta_value'     => $project_id,
));
```

### Getting Project Data

```php
// Get project meta
$project_id = 456;
$project_name = get_post_meta($project_id, '_project_name', true);
```

### Getting Structure Data

```php
// Get structures for a profile
$profile_id = 123;
$structures = get_posts(array(
    'post_type'      => 'profile_structure',
    'posts_per_page' => -1,
    'meta_key'       => '_structure_profile_id',
    'meta_value'     => $profile_id,
));

// Get structure details
foreach ($structures as $structure) {
    $tag = get_post_meta($structure->ID, '_structure_tag', true);
    $pictures = get_post_meta($structure->ID, '_structure_pictures', true);
    $description = get_post_meta($structure->ID, '_structure_description', true);
}
```

---

## Installation & Activation

### Installation Steps

1. **Upload Plugin:**
   - Upload folder `ecosys-profile-manager` to `/wp-content/plugins/`
   - Or: Use WordPress admin > Plugins > Add New > Upload

2. **Activate Plugin:**
   - Go to WordPress admin > Plugins
   - Find "Ecosys Profile Manager"
   - Click "Activate"

3. **Activation Hook:**
   - `register_activation_hook()` calls `Ecosys_Profile_Manager_Activator::activate()`
   - Currently no database changes required

### Post-Activation

After activation, the following appear in WordPress admin:
1. **Profiles** menu item (posts/profile)
2. **Projects** menu item (posts/project)
3. **Structures** submenu under Profiles

### Deactivation

- Go to WordPress admin > Plugins
- Find "Ecosys Profile Manager"
- Click "Deactivate"
- Deactivation hook calls cleanup (currently empty)
- All data remains in database

### Deletion

- In WordPress admin > Plugins
- Click "Delete" on "Ecosys Profile Manager"
- All plugin files removed
- All post type data (Profiles, Projects, Structures) remains

---

## Development Notes

### Constants Defined

```php
ECOSYS_PROFILE_MANAGER_VERSION = '1.0.0'        // Plugin version
ECOSYS_PROFILE_MANAGER_PATH    = plugin_dir_path(__FILE__)  // Plugin directory path
ECOSYS_PROFILE_MANAGER_URL     = plugin_dir_url(__FILE__)   // Plugin directory URL
```

### AJAX Security

All AJAX handlers include:
- Nonce verification (if applicable)
- User capability checks
- Input sanitization
- Prepared SQL queries (where applicable)

### Text Domain & i18n

All user-facing strings use:
```php
__('Text', 'ecosys-profile-manager')      // Translations
_e('Text', 'ecosys-profile-manager')      // Echo translations
_x('Text', 'context', 'ecosys-profile-manager')  // Context translations
_n('singular', 'plural', count, 'ecosys-profile-manager')  // Plurals
```

### Class Naming Convention

All classes follow WordPress plugin standards:
- Prefix: `Ecosys_Profile_Manager`
- File names: `class-ecosys-profile-manager-{purpose}.php`
- Class names: `Ecosys_Profile_Manager_{Purpose}`

---

## Future Enhancement Possibilities

1. **Front-End Display:**
   - Custom profile pages
   - Public archives
   - Profile search frontend

2. **Advanced Features:**
   - Profile relationships/hierarchies
   - Bulk import/export
   - Profile templates
   - Advanced filtering

3. **REST API:**
   - REST endpoints for profiles
   - REST endpoints for structures
   - Mobile app integration

4. **Reporting:**
   - Profile analytics
   - Export reports
   - Dashboard widgets

5. **Access Control:**
   - Custom capabilities per role
   - Profile visibility settings
   - Team/group management

---

## Support & Feedback

For issues, feature requests, or support, visit:
**https://ecosys.io**

---

*Documentation Generated: February 11, 2026*
*Plugin Version: 1.0.0*
