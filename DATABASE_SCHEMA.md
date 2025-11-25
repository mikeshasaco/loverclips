# LoverClips Database Schema

This document describes the complete database schema for the LoverClips application.

## Table of Contents

- [Users](#users)
- [Profiles](#profiles)
- [Posts](#posts)
- [Scenes](#scenes)
- [Scene Options](#scene-options)
- [Conversations](#conversations)
- [Conversation Messages](#conversation-messages)
- [Tips](#tips)
- [Purchases](#purchases)
- [Password Reset Tokens](#password-reset-tokens)
- [Sessions](#sessions)
- [Entity Relationships](#entity-relationships)

---

## Users

Stores user account information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `name` | string(255) | NOT NULL | User's full name |
| `username` | string(255) | UNIQUE, NULLABLE | Username for profile URL (e.g., `/profile/username`) |
| `email` | string(255) | UNIQUE, NOT NULL | User's email address |
| `email_verified_at` | timestamp | NULLABLE | Email verification timestamp |
| `password` | string(255) | NOT NULL | Hashed password |
| `remember_token` | string(100) | NULLABLE | Remember me token |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Has one `Profile`
- Has many `Posts`
- Has many `Conversations`
- Has many `Tips`
- Has many `Purchases`

---

## Profiles

Stores user profile information (one-to-one with users).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `user_id` | bigint unsigned | FOREIGN KEY → users.id, CASCADE DELETE | Reference to user |
| `bio` | text | NULLABLE | User biography |
| `location` | string(255) | NULLABLE | User's location |
| `avatar_url` | string(255) | NULLABLE | URL to profile picture |
| `banner_url` | string(255) | NULLABLE | URL to banner image |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Belongs to `User` (one-to-one)

---

## Posts

Stores interactive video clip posts created by users.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `user_id` | bigint unsigned | FOREIGN KEY → users.id, CASCADE DELETE | Post creator |
| `title` | string(255) | NOT NULL | Post title |
| `description` | text | NULLABLE | Post description |
| `category` | string(255) | NULLABLE | Post category |
| `thumbnail_url` | string(255) | NULLABLE | URL to thumbnail image |
| `price` | decimal(10,2) | NULLABLE | Price for paid posts |
| `is_paid` | boolean | DEFAULT false | Whether post requires payment |
| `is_published` | boolean | DEFAULT false | Whether post is published |
| `welcome_scene_id` | bigint unsigned | FOREIGN KEY → scenes.id, SET NULL ON DELETE | Reference to welcome scene |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Belongs to `User`
- Belongs to `Scene` (welcome_scene)
- Has many `Scenes`
- Has many `Conversations`
- Has many `Tips`
- Has many `Purchases`

---

## Scenes

Stores individual video "chat scenes" within a post. Each scene represents one clip + one message from the AI girlfriend.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `post_id` | bigint unsigned | FOREIGN KEY → posts.id, CASCADE DELETE | Parent post (girlfriend experience) |
| `video_url` | string(255) | NOT NULL | URL to video file |
| `title` | string(255) | NOT NULL | Internal scene title |
| `display_text` | text | NULLABLE | **Her chat message shown in the chat window** |
| `tip_prompt` | text | NULLABLE | Prompt text for tipping (optional) |
| `is_welcome` | boolean | DEFAULT false | Whether this is the welcome / entry scene |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Belongs to `Post`
- Has many `SceneOptions` (as source scene)
- Referenced by `SceneOptions` (as next scene)
- Referenced by `Conversations` (as current_scene)

---

## Scene Options

Stores reply options for a scene (pre-written "messages" the user can click).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `scene_id` | bigint unsigned | FOREIGN KEY → scenes.id, CASCADE DELETE | Source scene (where this option is shown) |
| `option_text` | string(255) | NOT NULL | Text displayed on the reply button (user's message) |
| `next_scene_id` | bigint unsigned | FOREIGN KEY → scenes.id, SET NULL ON DELETE | Target scene when this option is selected |
| `order` | integer | DEFAULT 1 | Display order (1,2,3,…) |
| `ai_intent_key` | string(100) | NULLABLE | Optional key like `flirty`, `comforting`, `spicy` for AI mapping |
| `requires_tokens` | boolean | DEFAULT false | Whether selecting this option is paywalled / premium |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Belongs to `Scene` (source scene)
- Belongs to `Scene` (next scene)
- Referenced by `ConversationMessages` (as option_id)

**Notes:**
- Each scene can have **any number** of options ordered by `order`
- Supports 1 option for linear flows, 2-3 options for branches, or more for complex interactions
- Options create branching paths through the interactive video experience
- `ai_intent_key` can be used for future AI-powered response generation
- `requires_tokens` allows premium/paywalled options

---

## Conversations

Stores user sessions with posts (one user chatting with one AI girlfriend experience).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `user_id` | bigint unsigned | FOREIGN KEY → users.id, CASCADE DELETE | Viewer / customer |
| `post_id` | bigint unsigned | FOREIGN KEY → posts.id, CASCADE DELETE | Post (girlfriend script) they're chatting with |
| `current_scene_id` | bigint unsigned | FOREIGN KEY → scenes.id, SET NULL ON DELETE | Current scene the conversation is at |
| `status` | string(50) | DEFAULT 'active' | `active`, `ended`, `paused`, etc. |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Belongs to `User`
- Belongs to `Post`
- Belongs to `Scene` (current_scene)
- Has many `ConversationMessages`

---

## Conversation Messages

Stores the actual chat log. Every bubble (her or him) is stored here.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `conversation_id` | bigint unsigned | FOREIGN KEY → conversations.id, CASCADE DELETE | Parent conversation |
| `user_id` | bigint unsigned | FOREIGN KEY → users.id, NULLABLE | For user messages (nullable for system/her messages) |
| `scene_id` | bigint unsigned | FOREIGN KEY → scenes.id, NULLABLE | Scene associated with this message (usually for her messages) |
| `option_id` | bigint unsigned | FOREIGN KEY → scene_options.id, NULLABLE | Option clicked (for user messages) |
| `sender_type` | string(20) | NOT NULL | `girl`, `user`, `system` |
| `text` | text | NOT NULL | The message text shown in chat |
| `video_url` | string(255) | NULLABLE | Video tied to this message (for her messages) |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Belongs to `Conversation`
- Belongs to `User` (optional, for user messages)
- Belongs to `Scene` (optional, for girl/system messages)
- Belongs to `SceneOption` (optional, for clicked options)

---

## Tips

Stores tip transactions from users to creators.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `user_id` | bigint unsigned | FOREIGN KEY → users.id, CASCADE DELETE | User who gave the tip |
| `post_id` | bigint unsigned | FOREIGN KEY → posts.id, CASCADE DELETE | Post that received the tip |
| `scene_id` | bigint unsigned | FOREIGN KEY → scenes.id, CASCADE DELETE | Scene that received the tip |
| `amount` | decimal(10,2) | NOT NULL | Tip amount |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Belongs to `User` (tipper)
- Belongs to `Post`
- Belongs to `Scene`

---

## Purchases

Stores post purchase transactions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `user_id` | bigint unsigned | FOREIGN KEY → users.id, CASCADE DELETE | User who purchased |
| `post_id` | bigint unsigned | FOREIGN KEY → posts.id, CASCADE DELETE | Post that was purchased |
| `amount` | decimal(10,2) | NOT NULL | Purchase amount |
| `stripe_payment_intent_id` | string(255) | NULLABLE | Stripe payment intent ID |
| `created_at` | timestamp | NULLABLE | Record creation timestamp |
| `updated_at` | timestamp | NULLABLE | Record update timestamp |

**Relationships:**
- Belongs to `User` (purchaser)
- Belongs to `Post`

---

## Password Reset Tokens

Stores password reset tokens (Laravel default).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `email` | string(255) | PRIMARY KEY | User's email address |
| `token` | string(255) | NOT NULL | Reset token |
| `created_at` | timestamp | NULLABLE | Token creation timestamp |

---

## Sessions

Stores user session data (Laravel default).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | string(255) | PRIMARY KEY | Session ID |
| `user_id` | bigint unsigned | FOREIGN KEY → users.id, NULLABLE, INDEXED | Associated user |
| `ip_address` | string(45) | NULLABLE | User's IP address |
| `user_agent` | text | NULLABLE | User's browser user agent |
| `payload` | longtext | NOT NULL | Session data |
| `last_activity` | integer | INDEXED | Last activity timestamp |

---

## Entity Relationships

### User Relationships
```
User
├── hasOne(Profile)
├── hasMany(Posts)
├── hasMany(Tips)
└── hasMany(Purchases)
```

### Post Relationships
```
Post
├── belongsTo(User)
├── belongsTo(Scene) [welcome_scene]
├── hasMany(Scenes)
├── hasMany(Tips)
└── hasMany(Purchases)
```

### Scene Relationships
```
Scene
├── belongsTo(Post)
├── hasMany(SceneOptions) [as source]
└── referencedBy(SceneOptions) [as next_scene]
```

### Scene Option Relationships
```
SceneOption
├── belongsTo(Scene) [source scene]
└── belongsTo(Scene) [next scene]
```

### Tip Relationships
```
Tip
├── belongsTo(User)
├── belongsTo(Post)
└── belongsTo(Scene)
```

### Purchase Relationships
```
Purchase
├── belongsTo(User)
└── belongsTo(Post)
```

### Conversation Relationships
```
Conversation
├── belongsTo(User)
├── belongsTo(Post)
├── belongsTo(Scene) [current_scene]
└── hasMany(ConversationMessages)
```

### Conversation Message Relationships
```
ConversationMessage
├── belongsTo(Conversation)
├── belongsTo(User) [for user messages]
├── belongsTo(Scene) [for girl/system messages]
└── belongsTo(SceneOption) [for clicked options]
```

---

## Foreign Key Constraints

| Foreign Key | References | On Delete | Description |
|-------------|------------|-----------|-------------|
| `profiles.user_id` | `users.id` | CASCADE | Delete profile when user is deleted |
| `posts.user_id` | `users.id` | CASCADE | Delete posts when user is deleted |
| `posts.welcome_scene_id` | `scenes.id` | SET NULL | Set to NULL if scene is deleted |
| `scenes.post_id` | `posts.id` | CASCADE | Delete scenes when post is deleted |
| `scene_options.scene_id` | `scenes.id` | CASCADE | Delete options when scene is deleted |
| `scene_options.next_scene_id` | `scenes.id` | SET NULL | Set to NULL if next scene is deleted |
| `conversations.user_id` | `users.id` | CASCADE | Delete conversations when user is deleted |
| `conversations.post_id` | `posts.id` | CASCADE | Delete conversations when post is deleted |
| `conversations.current_scene_id` | `scenes.id` | SET NULL | Set to NULL if scene is deleted |
| `conversation_messages.conversation_id` | `conversations.id` | CASCADE | Delete messages when conversation is deleted |
| `conversation_messages.user_id` | `users.id` | CASCADE | Delete user messages when user is deleted |
| `conversation_messages.scene_id` | `scenes.id` | SET NULL | Set to NULL if scene is deleted |
| `conversation_messages.option_id` | `scene_options.id` | SET NULL | Set to NULL if option is deleted |
| `tips.user_id` | `users.id` | CASCADE | Delete tips when user is deleted |
| `tips.post_id` | `posts.id` | CASCADE | Delete tips when post is deleted |
| `tips.scene_id` | `scenes.id` | CASCADE | Delete tips when scene is deleted |
| `purchases.user_id` | `users.id` | CASCADE | Delete purchases when user is deleted |
| `purchases.post_id` | `posts.id` | CASCADE | Delete purchases when post is deleted |
| `sessions.user_id` | `users.id` | NULLABLE | Sessions can exist without users |

---

## Indexes

- `users.email` - UNIQUE index
- `users.username` - UNIQUE index
- `sessions.user_id` - INDEX
- `sessions.last_activity` - INDEX

---

## Notes

1. **Username Field**: The `username` field in the `users` table is used for profile URLs (e.g., `/profile/username`). It must be unique and can contain letters, numbers, dashes, and underscores.

2. **Welcome Scene**: Each post can have a welcome scene that serves as the entry point. The `welcome_scene_id` in the `posts` table references a scene.

3. **Chat-Style Flow**: The application uses a chat-style interface where:
   - **Posts** = AI girlfriend experience / script / relationship container
   - **Scenes** = Individual video clips with chat messages (`display_text`)
   - **Scene Options** = Reply buttons users can click (supports any number of options)
   - **Conversations** = User sessions with a specific post
   - **Conversation Messages** = Chat log of all interactions

4. **Branching Logic**: Scenes can have any number of options (not limited to 2) that branch to different scenes, creating an interactive, choice-based video experience. Options are ordered by the `order` field.

5. **AI Integration Hooks**: The `ai_intent_key` field in `scene_options` allows mapping options to AI intents (e.g., `flirty`, `comforting`, `spicy`) for future AI-powered response generation.

6. **Premium Options**: The `requires_tokens` field in `scene_options` allows creators to paywall certain options, requiring users to purchase tokens/premium access.

7. **Payment Integration**: The `purchases` table stores Stripe payment intent IDs for tracking paid post purchases.

8. **File Storage**: URLs for videos, thumbnails, avatars, and banners are stored as strings. Actual file storage is handled separately (local storage or S3).

---

*Last Updated: November 19, 2025*

