module.exports = {
  port: 8001,
  host: '0.0.0.0',
  https_key: './examples/snakeoil.key',
  https_cert: './examples/snakeoil.crt',
  db_name: 'ethersheet',
  db_user: 'root',
  db_password: 'oxwall',
  db_host: 'mysql',
  db_type: 'mysql',
  debug: false,
  default_row_count: 100,
  default_col_count: 20,
  expire_days: 0,
  intro_text: "welcome to ethersheet, enter a sheet name to get started"
}
