#!/usr/bin/env python3
"""
VK Neuro-Agents - Test Scraping Script
Проверка работы scraping утилит
"""

import requests
from bs4 import BeautifulSoup
import json

def test_scrape():
    """Тест скрапинга"""
    print("============================================")
    print("🧪 Тестирование scraping утилит")
    print("============================================")
    print()
    
    # Тест 1: requests
    print("📦 Тест requests...")
    try:
        response = requests.get('https://httpbin.org/get', timeout=5)
        print(f"   ✅ requests работает: {response.status_code}")
    except Exception as e:
        print(f"   ❌ requests ошибка: {e}")
    print()
    
    # Тест 2: BeautifulSoup
    print("📦 Тест BeautifulSoup...")
    try:
        html = "<html><body><h1>Test</h1></body></html>"
        soup = BeautifulSoup(html, 'html.parser')
        title = soup.find('h1').text
        print(f"   ✅ BeautifulSoup работает: {title}")
    except Exception as e:
        print(f"   ❌ BeautifulSoup ошибка: {e}")
    print()
    
    # Тест 3: API тест
    print("📦 Тест API (локальный)...")
    try:
        response = requests.get('http://localhost:4000/health', timeout=5)
        data = response.json()
        print(f"   ✅ Backend API: {data.get('status', 'unknown')}")
    except Exception as e:
        print(f"   ⚠️  Backend недоступен: {e}")
    print()
    
    print("============================================")
    print("✅ Тестирование завершено!")
    print("============================================")

if __name__ == '__main__':
    test_scrape()
