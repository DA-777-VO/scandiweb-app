# Node.js для Vite dev сервера
FROM node:20-alpine

WORKDIR /app

# Копируем package.json первым — кэш слоя
COPY package.json ./

# Устанавливаем зависимости
RUN npm install

# Копируем остальной код
# (при bind mount в docker-compose это перезапишется содержимым хоста)
COPY . .

EXPOSE 3000

# Запускаем Vite dev сервер, слушаем на всех интерфейсах (0.0.0.0)
# без этого контейнер не будет доступен снаружи
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
