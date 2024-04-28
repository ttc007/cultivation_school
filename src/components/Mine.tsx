import { useState, useEffect } from 'react';
import { Card } from "./styled/styled";
import { useTonConnect } from "../hooks/useTonConnect";
import axios from 'axios';

export function Mine() {
  const { sender, connected, wallet } = useTonConnect();
  const [oreCount, setOreCount] = useState(null);

  useEffect(() => {
    const fetchOreCount = async () => {
      try {
        if (!connected) return;

        // Chuẩn bị dữ liệu để gửi đi
        const requestData = {
          user_id: wallet, // Thay đổi connected thành giá trị thực tế của user_id
          type: 'get_ore', // Loại nguyên thạch
        };

        // Gọi API để lấy số lượng ore
        axios.get('https://vayugo.000webhostapp.com/api/resources.php')
        .then(response => {
          if (!response.ok) {
            throw new Error('Failed to fetch ore count');
          }
          const data = response.json();
          setOreCount(data.count);
        })
        .catch(error => {
          // Xử lý lỗi nếu có
        });
        
      } catch (error) {
        console.error('Error fetching ore count:', error);
      }
    };

    if (connected) {
      fetchOreCount();
    }
  }, [connected]); // Thực hiện lại khi connected thay đổi

  return (
    <>
      {connected && (
        <Card>
          Mine! Ore Count: {oreCount}
        </Card>
      )}
    </>
  );
}
