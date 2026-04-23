FROM node:22-alpine

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

ENV HOST=0.0.0.0
ENV PORT=3000
ENV NODE_ENV=development

EXPOSE 3000

CMD ["npx", "nuxi", "dev", "--host", "0.0.0.0", "--port", "3000"]
