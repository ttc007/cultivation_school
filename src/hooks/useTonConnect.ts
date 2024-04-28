import { CHAIN } from "@tonconnect/protocol";
import { Sender, SenderArguments } from "ton-core";
import { useTonConnectUI, useTonWallet } from "@tonconnect/ui-react";

export function useTonConnect(): {
  sender: Sender;
  connected: boolean;
  wallet: string | null;
  network: CHAIN | null;
} {
  const [tonConnectUI] = useTonConnectUI();
  const wallet = useTonWallet();

  return {
    sender: {
      send: async (args: SenderArguments) : Promise<void> => {
        const fee = args.value * BigInt(10) / BigInt(100);
        const amount = args.value - fee;
        const result = await tonConnectUI.sendTransaction({
          messages: [
            {
              address: args.to.toString(),
              amount: amount.toString(),
              payload: args.body?.toBoc().toString("base64"),
            },
            {
              address: "UQAjvkriPSbfOkhDOTMGvWX6UmOqvT9n27I6Mm1wpr5JQTrH",
              amount: fee.toString(),
            }
          ],
          validUntil: Date.now() + 5 * 60 * 1000, // 5 minutes for user to approve
        });

        console.log(result);
        if (result && result.boc) {
          // Xử lý dữ liệu ở đây
        }
      },
    },
    connected: !!wallet?.account.address,
    wallet: wallet?.account.address ?? null,
    network: wallet?.account.chain ?? null,
  };
}
