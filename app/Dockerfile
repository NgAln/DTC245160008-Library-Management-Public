FROM php:8.2-cli-alpine

# Cài đặt extension kết nối PostgreSQL
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copy mã nguồn vào container
WORKDIR /app
COPY index.php /app/index.php

# Hardening: Tạo và gán quyền cho user non-root
RUN addgroup -S appgroup && adduser -S appuser -G appgroup \
    && chown -R appuser:appgroup /app
USER appuser

# Chạy web server tích hợp của PHP tại port 8000 (dùng port >1024 cho non-root user)
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"]
