module.exports = {
  apps: [
    {
      name: "whatsapp-gateway",
      script: "index.js",
      cwd: "c:/laragon/www/ProfilDesa/tools/whatsapp-gateway",
      autorestart: true,
      watch: false,
      max_memory_restart: "300M",
      out_file: "out.log",
      error_file: "err.log",
      merge_logs: true,
      time: true,
      env: {
        NODE_ENV: "production",
      },
    },
  ],
};
