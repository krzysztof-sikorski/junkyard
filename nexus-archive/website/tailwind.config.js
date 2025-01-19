module.exports = {
  content: [
    'templates/**/*.html.twig',
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms')({strategy: 'base'}),
  ],
  prefix: 'tw-',
}
