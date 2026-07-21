module.exports = {
  apps: [
    {
      name: "picme-realtime",
      script: "./server.js",
      instances: "max",       // Use all CPU cores available
      exec_mode: "cluster",   // Run in cluster mode
      watch: false,
      max_memory_restart: "1G",
      env: {
        NODE_ENV: "development",
        PORT: 3000,
        REDIS_HOST: "127.0.0.1",
        REDIS_PORT: 6379
      },
      env_production: {
        NODE_ENV: "production",
        PORT: 3000,
        REDIS_HOST: "127.0.0.1", // To be updated to Google Cloud Memorystore IP when deployed
        REDIS_PORT: 6379
      }
    },
    {
      name: "picme-queue",
      script: "artisan",
      args: "queue:work --tries=3 --delay=10 --timeout=90",
      interpreter: "php",
      instances: 2, // Lancer 2 workers de file d'attente
      exec_mode: "fork",
      watch: false,
      max_memory_restart: "512M",
      env: {
        APP_ENV: "development"
      },
      env_production: {
        APP_ENV: "production"
      }
    },
    {
      name: "picme-websockets",
      script: "artisan",
      args: "websockets:serve",
      interpreter: "php",
      instances: 1,
      exec_mode: "fork",
      watch: false
    }
  ]
};
