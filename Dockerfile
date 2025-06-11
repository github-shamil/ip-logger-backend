FROM php:8.1-cli

# Install needed PHP extensions
RUN docker-php-ext-install sockets

# Set working directory
WORKDIR /app

# Copy all files
COPY . .

# Expose port if needed (optional)
EXPOSE 80

# Command to run your logger
CMD ["php", "logger.php"]
