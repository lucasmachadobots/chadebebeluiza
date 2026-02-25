const mysql = require("mysql2/promise");

const headers = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Methods": "GET, OPTIONS",
  "Access-Control-Allow-Headers": "Content-Type",
  "Content-Type": "application/json; charset=utf-8"
};

exports.handler = async (event) => {
  if (event.httpMethod === "OPTIONS") {
    return { statusCode: 204, headers, body: "" };
  }

  if (event.httpMethod !== "GET") {
    return { statusCode: 405, headers, body: JSON.stringify({ success: false }) };
  }

  const { DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT } = process.env;

  if (!DB_HOST || !DB_USER || !DB_PASS || !DB_NAME) {
    return { statusCode: 500, headers, body: JSON.stringify({ success: false }) };
  }

  let conn;
  try {
    conn = await mysql.createConnection({
      host: DB_HOST,
      user: DB_USER,
      password: DB_PASS,
      database: DB_NAME,
      port: DB_PORT ? Number(DB_PORT) : 3306,
      charset: "utf8mb4"
    });

    const [rows] = await conn.execute(
      "SELECT id, nome, mensagem, created_at FROM recados WHERE aprovado = 1 ORDER BY id DESC LIMIT 30"
    );

    return { statusCode: 200, headers, body: JSON.stringify({ success: true, items: rows || [] }) };
  } catch {
    return { statusCode: 500, headers, body: JSON.stringify({ success: false }) };
  } finally {
    if (conn) await conn.end().catch(() => {});
  }
};
