# LoverClips - Interactive Video Platform

A gender-neutral interactive video platform built with Laravel 11 and Vue.js 3, where creators can upload short video clips and chain them into branching, choice-based experiences.

## Features

- **Interactive Video Experiences**: Create branching video stories with choice-based navigation
- **User Authentication**: Laravel Breeze with email/password authentication
- **Video Upload**: Support for 15-30 second video clips (local storage + S3 ready)
- **Monetization**: 
  - Paid posts (one-time purchase)
  - Tips during scenes
- **AI Integration**: AI-powered scene option generation (Phase 2)
- **Modern UI**: Vue.js 3 with Inertia.js and Tailwind CSS

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.3+)
- **Frontend**: Vue.js 3 + Inertia.js + Vite
- **Database**: PostgreSQL (configured in .env)
- **Authentication**: Laravel Breeze + Sanctum
- **Payments**: Stripe
- **Storage**: Local filesystem (S3 ready)
- **AI**: OpenAI GPT (optional, Phase 2)

## Installation

1. **Clone and install dependencies:**
```bash
cd loverclips
composer install
npm install
```

2. **Configure environment:**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Update `.env` file:**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=loverclips
DB_USERNAME=your_username
DB_PASSWORD=your_password

STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret

OPENAI_API_KEY=your_openai_api_key  # Optional, for AI features
```

4. **Run migrations:**
```bash
php artisan migrate
```

5. **Create storage link:**
```bash
php artisan storage:link
```

6. **Build assets:**
```bash
npm run build
# Or for development:
npm run dev
```

7. **Start the server:**
```bash
php artisan serve
```

## Project Structure

### Database Schema

- **users**: User accounts
- **profiles**: User profile information
- **posts**: Interactive video experiences
- **scenes**: Individual video clips within a post
- **scene_options**: Branching choices for each scene
- **tips**: Tips given by viewers
- **purchases**: Post purchases

### API Endpoints

#### Public
- `GET /api/posts` - List published posts
- `GET /api/posts/{id}` - Get post details

#### Protected (requires authentication)
- `POST /api/posts` - Create a post
- `PUT /api/posts/{id}` - Update a post
- `DELETE /api/posts/{id}` - Delete a post
- `GET /api/posts/{postId}/scenes` - List scenes for a post
- `POST /api/posts/{postId}/scenes` - Create a scene
- `GET /api/scenes/{id}` - Get scene details
- `PUT /api/scenes/{id}` - Update a scene
- `DELETE /api/scenes/{id}` - Delete a scene
- `POST /api/scenes/{id}/options` - Create scene option
- `PUT /api/options/{id}` - Update scene option
- `DELETE /api/options/{id}` - Delete scene option
- `POST /api/purchase` - Purchase a post
- `POST /api/tip` - Send a tip
- `POST /api/ai/scene-options` - Generate AI scene options

### Frontend Pages

- `/` - Homepage (list of posts)
- `/posts/{id}` - View interactive post
- `/dashboard` - User dashboard (Breeze default)
- `/profile` - User profile

## Usage

### For Creators

1. **Sign up / Log in**
2. **Create a Post:**
   - Set title, description, thumbnail
   - Set pricing (free or paid)
3. **Add Scenes:**
   - Upload 15-30 second video clips
   - Add title and optional tip prompt
   - Mark one scene as the welcome scene
4. **Add Choices:**
   - For each scene, add up to 2 options
   - Link each option to the next scene
   - Use AI to generate option texts (optional)
5. **Publish:** Set `is_published` to true

### For Viewers

1. **Browse Posts:** View all published posts on homepage
2. **Watch Interactive Videos:**
   - Click on a post thumbnail
   - If paid, purchase to unlock
   - Watch welcome scene
   - Make choices to navigate through the story
   - Tip creators during scenes

## Stripe Integration

1. Create a Stripe account and get API keys
2. Add keys to `.env` file
3. Set up webhook endpoint: `https://yourdomain.com/api/webhook/stripe`
4. Configure webhook to listen for `payment_intent.succeeded` events

## AI Features (Phase 2)

The AI integration allows creators to automatically generate scene option texts:

1. Upload a scene video
2. Optionally add a description
3. Click "Generate Options with AI"
4. AI suggests two option texts based on the scene context

## Storage

By default, videos and images are stored locally in `storage/app/public`. To use S3:

1. Configure AWS credentials in `.env`
2. Update `FILESYSTEM_DISK=s3` in `.env`
3. Update controllers to use S3 disk

## Development

```bash
# Run migrations
php artisan migrate

# Run migrations with fresh database
php artisan migrate:fresh

# Start development server
php artisan serve

# Watch for frontend changes
npm run dev

# Build for production
npm run build
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
