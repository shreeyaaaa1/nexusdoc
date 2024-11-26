# Quickstart Guide

This is a condensed guide to get the project running quickly. For more detailed instructions, see FINAL_SETUP.md.

## Quick Setup

1. **Install Requirements**
   - Install Docker Desktop (Windows/Mac) or Docker Engine + Docker Compose (Linux)

2. **Set Up Project**
   ```bash
   # Configure environment
   cp .env.example .env

   # Build and start services
   docker compose build
   docker compose up -d

   # Wait for MySQL to initialize (30 seconds)
   sleep 30

   # Create database tables
   docker compose exec mysql mysql -urcmwa_user -psecret_password rcmwa_db -e "
   CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );

   CREATE TABLE IF NOT EXISTS research_projects (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      title VARCHAR(255) NOT NULL,
      description TEXT,
      metadata JSON,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id)
      );
      CREATE TABLE IF NOT EXISTS reports (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      title VARCHAR(255) NOT NULL,
      type VARCHAR(50) NOT NULL,
      content TEXT,
      from_date DATE NOT NULL,
      to_date DATE NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
      );"

   

   
   ```

3. **Access Application**
   - Open http://localhost in your browser
   - Click "Register" to create an account
   - Log in with your credentials

## Verify It Works

1. After logging in, you should see the dashboard
2. Try these actions to verify everything works:
   - Create a new research project
   - Edit your profile
   - Upload a test file

## Quick Troubleshooting

If something goes wrong:

1. **View logs:**
   ```bash
   docker compose logs
   ```

2. **Restart everything:**
   ```bash
   docker compose restart
   ```

3. **Reset completely:**
   ```bash
   docker compose down -v
   docker compose up -d
   ```

4. **Fix permissions:**
   ```bash
   docker compose exec app chown -R www-data:www-data /var/www/html
   ```

## Stop the Application

```bash
docker compose down
```