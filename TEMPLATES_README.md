# Twig Templates for Backend Testing

## Overview
This comprehensive Twig template suite provides a complete UI for testing your Symfony-based Social Network backend. The templates cover all CRUD operations and test the full functionality of your Posts, Comments, and Reactions system.

## Template Structure

```
templates/
├── base.html.twig              # Enhanced base layout with responsive design
├── dashboard.html.twig         # Homepage/dashboard
├── post/
│   ├── index.html.twig        # List all posts with filtering
│   ├── show.html.twig         # View single post with comments and reactions
│   ├── new.html.twig          # Create new post form
│   └── edit.html.twig         # Edit existing post
├── comment/
│   ├── index.html.twig        # List all comments
│   ├── show.html.twig         # View single comment with context
│   ├── new.html.twig          # Create new comment form
│   └── edit.html.twig         # Edit existing comment
└── reaction/
    ├── index.html.twig        # List all reactions with distribution
    ├── new.html.twig          # Create new reaction form
    └── edit.html.twig         # Edit existing reaction
```

## Features

### Base Template
- **Responsive Navigation**: Quick access to all sections
- **Flash Messages**: Displays success/error notifications
- **Modern Styling**: Purple gradient theme with professional UI
- **Mobile-Friendly**: Adapts to different screen sizes

### Post Templates
| Template | Purpose | Tests |
|----------|---------|-------|
| index | List all posts with statistics | Read many, sorting, display |
| show | Full post view with comments/reactions | Read single, relationships |
| new | Create post with media | Create, forms, file handling |
| edit | Modify post content | Update, partial updates |

**Features tested:**
- ✅ Post content creation
- ✅ Visibility control (PUBLIC vs PATIENTS_ONLY)
- ✅ Media gallery (images/videos)
- ✅ Related comments display
- ✅ Reaction counts
- ✅ Edit tracking

### Comment Templates
| Template | Purpose | Tests |
|----------|---------|-------|
| index | List all comments with filtering | Read many, soft deletes |
| show | Complete comment with parent/replies | Read single, nested data |
| new | Add comment to a post | Create, relationships, forms |
| edit | Modify comment text | Update, edit tracking |

**Features tested:**
- ✅ Comment on posts
- ✅ Nested comments (replies)
- ✅ Soft delete implementation
- ✅ Edit status tracking
- ✅ Author tracking

### Reaction Templates
| Template | Purpose | Tests |
|----------|---------|-------|
| index | View reactions with distribution chart | Read many, grouping, stats |
| new | Add reaction to post | Create, unique constraints |
| edit | Change reaction type | Update, enum handling |

**Features tested:**
- ✅ 8 reaction types with emojis
- ✅ Unique constraint per user/post
- ✅ Reaction aggregation
- ✅ Emoji rendering

## Testing Workflows

### 1. **Create & Read Operations**
```
1. Navigate to /posts → + New Post
2. Fill in content, choose visibility, optionally add media
3. Submit → See success message
4. View in /posts/index
5. Click View → See /posts/{id}
```

### 2. **Comments Testing**
```
1. From POST SHOW page → Click "Add Comment"
2. Fill form with post, user ID, comment text
3. Optionally select parent comment for nesting
4. View in /comments/index
5. Reply chain testing in /comments/{id}
```

### 3. **Reactions Testing**
```
1. From /reactions/new → Select post, user ID
2. Choose reaction type (emoji-based)
3. View distribution in /reactions/index
4. Test unique constraint (edit instead of duplicate)
```

### 4. **Edit & Delete Operations**
```
1. /posts/{id}/edit → Modify content → Submit
2. /comments/{id}/edit → Update text (marks as edited)
3. /comments/{id} → Delete button (soft delete)
4. /reactions/{id}/edit → Change reaction type
```

## Data Relationships Tested

```
Post (1) ──────── (Many) Comment
  │
  ├─→ (Many) Reaction
  │
  └─→ (Many) PostMedia

Comment (1) ────── (Many) Comment (replies)
  │
  └─→ (1) Post
```

## Form Elements Covered

### Post Form
- Text area (rich content)
- Select field (visibility enum)
- Collection field (media items)

### Comment Form
- Entity selector (dropdown for Posts)
- Number input (User ID)
- Text area (content)
- Entity selector (optional parent comment)

### Reaction Form
- Entity selector (Post)
- Number input (User ID)
- Choice field (8 reaction type options)

## UI Components Demonstrating

- ✅ Alert messages (success/info/danger)
- ✅ Badges and labels
- ✅ Stat cards with numbers
- ✅ Empty states
- ✅ Breadcrumb navigation
- ✅ Media gallery grid
- ✅ Nested comment threads
- ✅ Reaction emoji display
- ✅ Dynamic stats calculation
- ✅ Form error handling
- ✅ CSRF token implementation

## Database Constraints Tested

✅ Post visibility default to PATIENTS_ONLY
✅ Created/updated timestamps auto-managed
✅ Soft delete flags (is_deleted, deleted_at)
✅ Edit tracking (is_edited)
✅ Unique constraint on Reaction (user + post)
✅ Cascade delete from Post to Comments/Reactions
✅ Orphan removal for PostMedia

## Backend API Routes Used

**Posts:**
- GET /posts (index)
- GET /posts/new (form)
- POST /posts (create)
- GET /posts/{id} (show)
- GET /posts/{id}/edit (form)
- POST /posts/{id} (update)

**Comments:**
- GET /comments (index)
- GET /comments/new (form)
- POST /comments (create)
- GET /comments/{id} (show)
- GET /comments/{id}/edit (form)
- POST /comments/{id} (update)
- POST /comments/{id}/delete

**Reactions:**
- GET /reactions (index)
- GET /reactions/new (form)
- POST /reactions (create)
- GET /reactions/{id}/edit (form)
- POST /reactions/{id} (update)
- POST /reactions/{id}/delete

## Styling Features

- **Gradient backgrounds**: Primary color scheme (#667eea to #764ba2)
- **Responsive grid layouts**: Auto-fit columns
- **Hover effects**: Interactive buttons and cards
- **Color-coded badges**: Status indicators
- **Card-based layout**: Clean white cards on gradient background
- **Mobile optimization**: Flex layout adjustments

## Next Steps

1. **Add Authentication**: Integrate Security bundle for real user tracking
2. **Implement API**: Keep REST endpoints alongside Twig templates
3. **Add Testing**: Unit tests using these templates as reference
4. **Enhance Forms**: Add client-side validation
5. **Real User Association**: Replace hardcoded user IDs with authenticated users
6. **Search/Filter**: Add post search and comment filtering
7. **Notifications**: Implement real-time updates for reactions/comments
8. **Pagination**: Add limit/offset to large lists

## Form Submission Testing Tips

- **Test with invalid data**: Leave required fields empty
- **Test cascading deletes**: Delete a post and verify related comments/reactions are removed
- **Test unique constraints**: Try adding duplicate reactions from same user
- **Test relationships**: Verify parent comments correctly reference child comments
- **Test soft deletes**: View comment list after deletion (should show [Deleted] state)

---

**Created**: April 2024  
**Framework**: Symfony 6.4 + Twig  
**Database**: MySQL 8.0  
**Status**: Ready for comprehensive backend testing
