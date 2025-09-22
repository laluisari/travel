# Testing QRIS Midtrans Sandbox

## 🔍 Problem & Solution

**Problem:** QR Code URL dari Midtrans sandbox tidak bisa langsung digunakan untuk payment simulation.

**Explanation:** 
- QR Code URL (`https://api.sandbox.midtrans.com/v2/qris/xxx/qr-code`) adalah untuk **display purposes only**
- Payment simulator membutuhkan **Order ID** atau **Transaction ID**, bukan QR Code URL
- Sandbox environment menggunakan simulation, bukan real payment

## 🧪 Cara Testing QRIS di Sandbox

### 1. **Generate QRIS Transaction**
- Lakukan booking manual dengan payment method "QRIS"
- Sistem akan generate QR Code dan Transaction ID
- Catat **Order ID** (booking_code) yang muncul di halaman

### 2. **Simulate Payment**

#### Option A: Midtrans Payment Simulator
1. Buka: https://simulator.sandbox.midtrans.com/qris/index
2. Masukkan **Order ID** (bukan QR URL)
3. Klik "Pay" untuk simulate successful payment
4. Sistem akan otomatis update status via webhook

#### Option B: Manual Status Update
1. Buka Midtrans Dashboard: https://dashboard.sandbox.midtrans.com/
2. Login dengan akun Midtrans Anda
3. Cari transaction berdasarkan Order ID
4. Update status manually ke "settlement"

### 3. **Verify Payment Status**
- Cek di halaman booking detail
- Atau call API: `GET /api/payment-status/{order_id}`

## 📋 Testing Flow

```
1. Admin → Manual Booking → Pilih QRIS
2. Sistem → Generate QR Code & Order ID  
3. Copy Order ID (contoh: BOOK-123456)
4. Buka Payment Simulator
5. Input Order ID → Submit "Pay"
6. Webhook → Update Status → Booking Paid ✅
```

## 🔧 Debug Information

Di development mode, halaman QRIS akan menampilkan:
- Order ID untuk testing
- Transaction ID  
- QR Code URL (display only)
- Link ke payment simulator
- Instruksi testing lengkap

## 📞 API Endpoints

- **Check Status:** `GET /api/payment-status/{order_id}`
- **Webhook:** `POST /api/midtrans/webhook`

## ⚠️ Important Notes

1. **QR Code URL** hanya untuk ditampilkan ke customer
2. **Testing** menggunakan Order ID di payment simulator
3. **Production** akan menggunakan real QR scanning
4. **Webhook** akan otomatis update status payment

---

## 🎯 Next Steps

1. ✅ QRIS implementation ready
2. ⏳ Webhook configured (test with simulator)
3. ⏳ Production deployment (with real QR scanning)
