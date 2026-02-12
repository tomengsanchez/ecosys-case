## Ecosys Profile Manager – Capability & Role Guide

This guide explains how **roles** and **capabilities** are used in this plugin, and what to do **when adding new custom post types or admin pages/submenus** so that permissions stay consistent.

---

### Core concepts

- **Primary custom role**: `Ecosys Admin` (`ecosys_admin`)
  - Created/updated on plugin activation in `Ecosys_Profile_Manager_Activator::activate()`.
  - Gets the custom capability:
    - `manage_ecosys_profile_manager`

- **Core capability**: `manage_ecosys_profile_manager`
  - This is the **single capability** that grants full access to all **Ecosys Profile Manager** admin functionality:
    - All plugin admin menus/submenus.
    - All plugin custom post types (Profiles, Projects, Profile Structures, and any future CPTs).
  - The **`administrator`** role is also granted this capability on activation so site admins always retain full control.

Kapag gumagawa ng bagong features (CPTs, submenu pages, settings, tools), lagi dapat gamitin ang **`manage_ecosys_profile_manager`** para consistent ang access control.

---

### 1. Custom Post Types (CPTs)

File: `includes/class-ecosys-profile-manager-cpt.php`

- The CPT class defines a **core capability** property:

```php
private $core_capability = 'manage_ecosys_profile_manager';
```

- It also defines a **shared capabilities mapping** used by all plugin CPTs:

```php
private function get_custom_capabilities() {
    return array(
        'edit_post'              => $this->core_capability,
        'read_post'              => 'read',
        'delete_post'            => $this->core_capability,
        'edit_posts'             => $this->core_capability,
        'edit_others_posts'      => $this->core_capability,
        'publish_posts'          => $this->core_capability,
        'read_private_posts'     => $this->core_capability,
        'delete_posts'           => $this->core_capability,
        'delete_private_posts'   => $this->core_capability,
        'delete_published_posts' => $this->core_capability,
        'delete_others_posts'    => $this->core_capability,
        'edit_private_posts'     => $this->core_capability,
        'edit_published_posts'   => $this->core_capability,
    );
}
```

- Existing CPTs (`profile`, `project`, `profile_structure`) are registered with:

```php
'capability_type'    => 'post',
'map_meta_cap'       => false,
'capabilities'       => $this->get_custom_capabilities(),
```

#### When adding a NEW CPT

1. **Use the shared capabilities mapping**:
   - In your new `register_*_post_type()` method, set:

   ```php
   'capability_type'    => 'post',
   'map_meta_cap'       => false,
   'capabilities'       => $this->get_custom_capabilities(),
   ```

2. **Do NOT invent new capabilities** unless you need a more complex permission model.
   - If you ever add new caps, make sure to:
     - Update the activator to grant them to `ecosys_admin` and `administrator`.
     - Document them here.

Result: anyone with `manage_ecosys_profile_manager` (e.g. Ecosys Admins) can **manage all plugin CPT content**, including future ones.

---

### 2. Admin Menus and Submenus

File: `admin/class-ecosys-profile-manager-menu.php`

- The main plugin menu and submenus use **`manage_ecosys_profile_manager`** as their capability:

```php
$capability = 'manage_ecosys_profile_manager';

add_menu_page(
    __( 'Ecosys Profile Management', 'ecosys-profile-manager' ),
    __( 'Ecosys Profile Management', 'ecosys-profile-manager' ),
    $capability,
    $this->menu_slug,
    array( $this, 'render_dashboard_page' ),
    'dashicons-groups',
    30
);

add_submenu_page(
    $this->menu_slug,
    __( 'Dashboard', 'ecosys-profile-manager' ),
    __( 'Dashboard', 'ecosys-profile-manager' ),
    $capability,
    $this->menu_slug,
    array( $this, 'render_dashboard_page' )
);

add_submenu_page(
    $this->menu_slug,
    __( 'Settings', 'ecosys-profile-manager' ),
    __( 'Settings', 'ecosys-profile-manager' ),
    $capability,
    $this->menu_slug . '-settings',
    array( $this, 'render_settings_page' )
);
```

#### When adding a NEW submenu or admin page

- Always use the same capability:

```php
$capability = 'manage_ecosys_profile_manager';

add_submenu_page(
    $this->menu_slug,
    __( 'New Tool', 'ecosys-profile-manager' ),
    __( 'New Tool', 'ecosys-profile-manager' ),
    $capability,
    $this->menu_slug . '-new-tool',
    array( $this, 'render_new_tool_page' )
);
```

This ensures:

- **Ecosys Admins** (and admins) see all plugin-related menus/tools.
- Other roles won’t accidentally get access unless explicitly granted the capability.

---

### 3. Other checks (metaboxes, AJAX, etc.)

For any new functionality inside the plugin that should be restricted to Ecosys admins, use:

```php
if ( ! current_user_can( 'manage_ecosys_profile_manager' ) ) {
    wp_die( esc_html__( 'You do not have permission to access this page.', 'ecosys-profile-manager' ) );
}
```

Use this pattern for:

- **Custom admin pages or tools**.
- **AJAX handlers** (`wp_ajax_*` actions).
- **Metabox save callbacks** if they should be restricted.

---

### 4. Summary

- **Role**: Use `Ecosys Admin` (`ecosys_admin`) for users who should manage everything related to this plugin.
- **Core capability**: `manage_ecosys_profile_manager` is the single source of truth for **all plugin admin access**.
- **New CPTs**: Always use `$this->get_custom_capabilities()` (or `$this->core_capability`) for capabilities.
- **New menus/submenus and tools**: Always require `manage_ecosys_profile_manager`.

Kapag may bagong feature (CPT, submenu, settings page, tools), siguraduhing:

1. Naka-base sa `manage_ecosys_profile_manager` ang access.
2. Kung may bagong capability, idagdag sa activator at i-document dito sa `GUIDE.MD`.

