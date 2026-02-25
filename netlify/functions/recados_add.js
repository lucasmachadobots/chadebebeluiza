const mysql = require("mysql2/promise");

const headers = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Methods": "POST, OPTIONS",
  "Access-Control-Allow-Headers": "Content-Type",
  "Content-Type": "application/json; charset=utf-8"
};

exports.handler = async (event) => {
  if (event.httpMethod === "OPTIONS") {
    return {
      statusCode: 204,
      headers,
      body: ""
    };
  }

  if (event.httpMethod !== "POST") {
    return {
      statusCode: 405,
      headers,
      body: JSON.stringify({ success: false })
    };
  }

  let data;
  try {
    data = JSON.parse(event.body || "{}");
  } catch {
    return {
      statusCode: 400,
      headers,
      body: JSON.stringify({ success: false })
    };
  }

  const nome = String(data.nome || "").trim().slice(0, 120);
  const whatsapp = String(data.whatsapp || "").trim().slice(0, 40);
  const mensagem = String(data.mensagem || "").trim().slice(0, 1000);

  if (!nome || !mensagem) {
    return {
      statusCode: 422,
      headers,
      body: JSON.stringify({ success: false })
    };
  }

  const {
    DB_HOST,
    DB_USER,
    DB_PASS,
    DB_NAME,
    DB_PORT
  } = process.env;

  if (!DB_HOST || !DB_USER || !DB_PASS || !DB_NAME) {
    return {
      statusCode: 500,
      headers,
      body: JSON.stringify({ success: false })
    };
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

    const ip =
      event.headers["x-forwarded-for"] ||
      event.headers["client-ip"] ||
      "";

    const ua = event.headers["user-agent"] || "";

    await conn.execute(
      "INSERT INTO recados (nome, whatsapp, mensagem, aprovado, ip, user_agent) VALUES (?, ?, ?, 1, ?, ?)",
      [nome, whatsapp, mensagem, ip, ua]
    );

    return {
      statusCode: 200,
      headers,
      body: JSON.stringify({ success: true })
    };
  } catch {
    return {
      statusCode: 500,
      headers,
      body: JSON.stringify({ success: false })
    };
  } finally {
    if (conn) {
      await conn.end().catch(() => {});
    }
  }
};