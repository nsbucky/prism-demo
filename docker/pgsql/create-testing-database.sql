SELECT 'CREATE DATABASE testing'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'testing')\gexec

\c testing
CREATE EXTENSION IF NOT EXISTS vector;
