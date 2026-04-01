# Smart Duplicator

**Duplicate WordPress posts, pages, and custom post types with a single click.**

A lightweight, clean, open-source alternative to Duplicate Post / Yoast Duplicate Post — no bloat, no upsells, fully hookable.

---

## Features

- ✅ **Duplicate any post type** — posts, pages, and all registered public CPTs
- ✅ **One-click row action** — "Duplicate" appears in every list table
- ✅ **Bulk duplicate** — select many posts and duplicate them all at once
- ✅ **Copies meta, taxonomies, featured image** — fully configurable per setting
- ✅ **Configurable title suffix** — e.g. " (Copy)", " – Draft", or blank
- ✅ **Choose post status** — draft, published, pending, or private
- ✅ **REST API endpoint** — `POST /wp-json/smart-duplicator/v1/duplicate/{id}`
- ✅ **Developer hooks** — filter meta keys to skip, act after duplication
- ✅ **GPL v2 licensed** — truly free and open source

---

## Installation

### From ZIP
1. Download the latest release ZIP.
2. Go to **Plugins → Add New → Upload Plugin**.
3. Activate.

### Manually
```bash
git clone https://github.com/your-repo/smart-duplicator.git wp-content/plugins/smart-duplicator
```
Then activate from **Plugins → Installed Plugins**.

---

## Usage

### Row Action
In any post list (**Posts**, **Pages**, or a CPT list), hover a row — click **Duplicate**.

### Bulk Action
Select multiple posts → **Bulk Actions → Duplicate → Apply**.

### REST API
```http
POST /wp-json/smart-duplicator/v1/duplicate/123
Authorization: Basic <credentials>

{
  "status": "draft",
  "title_suffix": " (Copy)"
}
```

Returns:
```json
{
  "id": 456,
  "title": "My Post (Copy)",
  "status": "draft",
  "edit_link": "https://yoursite.com/wp-admin/post.php?post=456&action=edit",
  "link": "https://yoursite.com/?p=456"
}
```

---

## Developer Hooks

### Filter: skip certain meta keys
```php
add_filter( 'smart_duplicator_skip_meta_keys', function( $keys ) {
    $keys[] = '_my_custom_key_to_skip';
    return $keys;
} );
```

### Action: after duplication
```php
add_action( 'smart_duplicator_after_duplicate', function( $new_id, $original_id, $source_post, $opts ) {
    // e.g. log, send notification, run custom logic
}, 10, 4 );
```

### Programmatic duplication
```php
$new_id = Smart_Duplicator::duplicate( 123, [
    'status'       => 'draft',
    'title_suffix' => ' (Copy)',
    'copy_meta'    => true,
    'copy_terms'   => true,
] );
```

---

## Settings

Go to **Settings → Smart Duplicator** to configure:

| Option | Description |
|---|---|
| Supported Post Types | Which post types show the Duplicate action |
| Duplicate Status | Status of the new copy (draft / publish / pending / private) |
| Title Suffix | Text appended to the duplicated post's title |
| After Duplicating | Open editor or stay on list |
| Copy Meta | Copy all custom fields |
| Copy Terms | Copy categories, tags & custom taxonomies |
| Copy Featured Image | Copy the thumbnail assignment |

---

## Requirements

- WordPress 5.5+
- PHP 7.4+

---

## License

GPL v2 or later. See [LICENSE](LICENSE) for full text.
