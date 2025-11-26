
# Web Forum

Sebuah platform forum diskusi online yang dibangun dengan **React.js** dan **Node.js**. Aplikasi ini memungkinkan pengguna untuk berpartisipasi dalam diskusi forum dengan berbagai fitur interaktif.

## ✨ Fitur

- **Autentikasi Pengguna**
  - Registrasi dan login pengguna
  - Sistem autentikasi JWT
  - Proteksi rute berdasarkan role

- **Manajemen Forum**
  - Buat, edit, dan hapus thread
  - Posting komentar dan balasan
  - Kategori forum yang terorganisir

- **Fitur Interaktif**
  - Voting (like/dislike) pada thread dan komentar
  - Bookmark thread favorit
  - Follow pengguna lain
  - Sistem reputasi pengguna

- **Antarmuka Pengguna**
  - Responsive design
  - Dark/Light mode
  - Real-time updates
  - Optimized for mobile devices

## 🛠 Teknologi

### Frontend
- **React.js** - UI framework
- **Tailwind CSS** - Styling
- **React Router** - Navigation
- **Axios** - HTTP client
- **React Query** - State management

### Backend
- **Node.js** - Runtime environment
- **Express.js** - Web framework
- **JWT** - Authentication
- **bcrypt** - Password hashing
- **CORS** - Cross-origin resource sharing

## 🚀 Instalasi dan Menjalankan

### Prerequisites
- Node.js (versi 14 atau lebih tinggi)
- npm atau yarn

### Langkah-langkah

1. **Clone repository**
   ```bash
   git clone https://github.com/Ryuzxy/Web-Forum.git
   cd Web-Forum
   ```

2. **Install dependencies**
   ```bash
   # Install frontend dependencies
   cd client
   npm install

   # Install backend dependencies  
   cd ../server
   npm install
   ```

3. **Konfigurasi Environment**
   - Buat file `.env` di folder server
   - Tambahkan variabel environment yang diperlukan (JWT_SECRET, dll)

4. **Jalankan aplikasi**
   ```bash
   # Terminal 1 - Backend
   cd server
   npm run dev

   # Terminal 2 - Frontend  
   cd client
   npm start
   ```

5. **Akses aplikasi**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:5000

## 📁 Struktur Proyek

```
Web-Forum/
├── client/                 # Frontend React application
│   ├── src/
│   │   ├── components/    # Reusable components
│   │   ├── pages/        # Page components
│   │   ├── hooks/        # Custom hooks
│   │   └── utils/        # Utility functions
│   └── public/           # Static assets
├── server/               # Backend Node.js application
│   ├── controllers/      # Route controllers
│   ├── middleware/       # Custom middleware
│   ├── models/          # Database models
│   ├── routes/          # API routes
│   └── config/          # Configuration files
└── README.md
```

## 🔧 Scripts yang Tersedia

### Frontend
```bash
npm start      # Jalankan development server
npm run build  # Build untuk production
npm test       # Jalankan test suite
```

### Backend
```bash
npm run dev    # Jalankan development server dengan nodemon
npm start      # Jalankan production server
```

## 🤝 Kontribusi

Kontribusi selalu diterima! Silakan:

1. Fork project ini
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📝 Lisensi

Distributed under the MIT License. Lihat `LICENSE` untuk informasi lebih lanjut.

## 📞 Kontak

Ryuzxy - [GitHub](https://github.com/Ryuzxy)

Link Project: [https://github.com/Ryuzxy/Web-Forum](https://github.com/Ryuzxy/Web-Forum)

---

⭐ Beri bintang pada repository ini jika Anda merasa project ini bermanfaat!
