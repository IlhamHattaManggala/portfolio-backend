# Admin Dashboard & API Documentation

## Setup

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Create Storage Link** (untuk file upload)
   ```bash
   php artisan storage:link
   ```

## Admin Dashboard

Setelah login, Anda dapat mengakses admin dashboard melalui:

- **Dashboard**: `/dashboard` - Overview dengan statistik
- **Projects**: `/admin/projects` - Manage portfolio projects
- **Technologies**: `/admin/technologies` - Manage technologies/skills
- **Certificates**: `/admin/certificates` - Manage certificates

### Features:
- ✅ CRUD operations untuk semua data
- ✅ Image upload untuk projects, technologies, dan certificates
- ✅ Active/Inactive toggle
- ✅ Order management
- ✅ Technology assignment untuk projects
- ✅ Real-time updates dengan Livewire

## API Endpoints

### Public API (No Authentication Required)

#### Get All Projects
```
GET /api/v1/projects
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Project Name",
      "descriptions": "Project description",
      "tipe": "Website",
      "library": ["React", "Laravel"],
      "image": "http://domain.com/storage/projects/image.jpg",
      "order": 0,
      "is_active": true,
      "technologies": [...],
      "created_at": "...",
      "updated_at": "..."
    }
  ]
}
```

#### Get Single Project
```
GET /api/v1/projects/{id}
```

#### Get All Technologies
```
GET /api/v1/technologies
```

#### Get Single Technology
```
GET /api/v1/technologies/{id}
```

#### Get All Certificates
```
GET /api/v1/certificates
```

#### Get Single Certificate
```
GET /api/v1/certificates/{id}
```

### Admin API (Authentication Required)

Semua endpoint di bawah ini memerlukan authentication (login terlebih dahulu).

#### Projects Admin API
```
POST   /api/v1/admin/projects       - Create project
PUT    /api/v1/admin/projects/{id}   - Update project
DELETE /api/v1/admin/projects/{id}  - Delete project
```

#### Technologies Admin API
```
POST   /api/v1/admin/technologies       - Create technology
PUT    /api/v1/admin/technologies/{id}  - Update technology
DELETE /api/v1/admin/technologies/{id}  - Delete technology
```

#### Certificates Admin API
```
POST   /api/v1/admin/certificates       - Create certificate
PUT    /api/v1/admin/certificates/{id}  - Update certificate
DELETE /api/v1/admin/certificates/{id}  - Delete certificate
```

## Example API Usage

### Create Project (Admin)
```bash
curl -X POST http://your-domain.com/api/v1/admin/projects \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=My Project" \
  -F "descriptions=Project description" \
  -F "tipe=Website" \
  -F "library[]=React" \
  -F "library[]=Laravel" \
  -F "image=@/path/to/image.jpg" \
  -F "order=0" \
  -F "technology_ids[]=1" \
  -F "technology_ids[]=2"
```

### Update Project (Admin)
```bash
curl -X PUT http://your-domain.com/api/v1/admin/projects/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Updated Project Name" \
  -F "is_active=true"
```

## Database Structure

### Projects Table
- `id` - Primary key
- `name` - Project name
- `descriptions` - Project description
- `tipe` - Project type (Website, Mobile, etc.)
- `library` - JSON array of technologies/libraries
- `image` - Image path
- `order` - Display order
- `is_active` - Active status
- `created_at`, `updated_at`

### Technologies Table
- `id` - Primary key
- `name` - Technology name
- `icon` - Icon image path
- `order` - Display order
- `is_active` - Active status
- `created_at`, `updated_at`

### Certificates Table
- `id` - Primary key
- `title` - Certificate title
- `platform` - Platform name (Dicoding, Huawei, etc.)
- `image` - Certificate image path
- `order` - Display order
- `is_active` - Active status
- `created_at`, `updated_at`

### Project Technology (Pivot Table)
- `project_id` - Foreign key to projects
- `technology_id` - Foreign key to technologies

## Notes

- Semua image akan disimpan di `storage/app/public/`
- Pastikan storage link sudah dibuat dengan `php artisan storage:link`
- API menggunakan JSON format untuk response
- File upload menggunakan multipart/form-data
- Admin endpoints memerlukan authentication middleware

