/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{js,jsx,ts,tsx}"],
  theme: {
    extend: {
      colors: {
        vote: {
          primary: '#729e81',   /* 60% Dominant */
          secondary: '#00a78e', /* 30% Interactive */
          accent: '#00a1d8',    /* 10% Call to Action */
        }
      }
    },
  },
  plugins: [],
}