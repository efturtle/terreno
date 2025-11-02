# Terreno - Mexican Real Estate API

> 🏠 A comprehensive property management API built with Laravel, specifically designed for the Mexican real estate market.

## 🚀 Quick Start

```bash
# Clone and setup
git clone https://github.com/efturtle/terreno.git
cd terreno
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Start the API server
php artisan serve
```

## 📖 Documentation

- **[📋 API Documentation](./API_DOCUMENTATION.md)** - Complete API reference with examples
- **[🏗️ Architecture Guide](./ARCHITECTURE_EXPLAINED.md)** - Understanding the code structure
- **[🇲🇽 Mexican Address System](./MEXICAN_ADDRESSES.md)** - Localized address generation

## ✨ Features

- **🏠 Property Management** - Full CRUD operations for real estate properties
- **🇲🇽 Mexican Localization** - Authentic Mexican addresses, states, and cities
- **🔍 Advanced Search** - Filter by location, price, property type, and more
  - **Smart Bedroom/Bathroom Search** - Search for "3 bedrooms" returns 3+ bedroom properties
- **📊 Analytics** - Property statistics and market insights
- **🛡️ Robust Validation** - Comprehensive data validation with Spanish error messages
- **🧪 Test Coverage** - Extensive test suite ensuring reliability
- **📱 API-First Design** - Clean JSON responses ready for mobile and web apps

## 🏠 Property Features

- **Property Types**: Casa, Condominio, Departamento, Townhouse, Duplex
- **Status Tracking**: Disponible, Pendiente, Vendida, Rentada
- **Mexican Geography**: 32 states with authentic cities and postal codes
- **Rich Metadata**: Square footage, bedrooms, bathrooms, amenities, and more

## 🛠️ Technology Stack

- **Laravel 12** - Modern PHP framework
- **SQLite** - Lightweight database (easily scalable to PostgreSQL/MySQL)
- **PHPUnit** - Comprehensive testing
- **Laravel Pint** - Code formatting
- **API Resources** - Clean, consistent JSON responses

## 📊 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/properties` | List properties with filtering |
| `POST` | `/api/properties` | Create new property |
| `GET` | `/api/properties/{id}` | Get single property |
| `PUT` | `/api/properties/{id}` | Update property |
| `DELETE` | `/api/properties/{id}` | Delete property |
| `GET` | `/api/properties/search` | Advanced search |
| `GET` | `/api/properties/stats` | Market statistics |

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test groups
php artisan test --filter=PropertyApiTest
php artisan test --filter=MexicanAddressTest
```

## 🌍 Environment Configuration

```env
# Mexican configuration (default)
APP_COUNTRY_CODE=MX
APP_POSTAL_PREFIX=MX-
APP_CURRENCY=MXN
```

## 🤝 Contributing

This is a personal project, but feel free to fork and adapt for your own real estate needs!

## 📄 License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
