importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyBANwr3eUpU_tdaSXxIkv052raJefEioUg",
  authDomain: "himish-9d505.firebaseapp.com",
  projectId: "himish-9d505",
  storageBucket: "himish-9d505.appspot.com",
  messagingSenderId: "709596707451",
  appId: "1:709596707451:web:1dabc003797d3123c080de"
});

const messaging = firebase.messaging();

// Handle background notifications
messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  const { title, body } = payload.notification;
  self.registration.showNotification(title, { body });
});
